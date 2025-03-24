<?php

namespace App\Filters;

use App\Contracts\Filters\FilterInterface;
use App\Enums\AttributeType;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EavFilter implements FilterInterface
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    const CACHE_TTL = 300;

    /**
     * Apply the EAV filter to the query
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value) || ! is_array($value)) {
            return $query;
        }

        $attributeName = $value['name'] ?? null;
        $operator = $value['operator'] ?? '=';
        $attributeValue = $value['value'] ?? null;

        // Use cache to avoid repeated database lookups for attributes
        $cacheKey = "attribute_{$attributeName}";
        $attribute = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($attributeName) {
            return Attribute::query()
                ->where('name', $attributeName)
                ->whereIn('type', [AttributeType::TEXT, AttributeType::NUMBER, AttributeType::BOOLEAN, AttributeType::SELECT, AttributeType::DATE])
                ->first();
        });

        if (! $attribute) {
            return $query;
        }

        return match ($attribute->type) {
            AttributeType::TEXT => $this->handleTextFilter($query, $attribute, $value),
            AttributeType::NUMBER => $this->handleNumberFilter($query, $attribute, $value),
            AttributeType::BOOLEAN => $this->handleBooleanFilter($query, $attribute, $value),
            AttributeType::SELECT => $this->handleSelectFilter($query, $attribute, $value),
            AttributeType::DATE => $this->handleDateFilter($query, $attribute, $value),
            default => $query,
        };
    }

    /**
     * Get the name of the filter.
     */
    public function getName(): string
    {
        return 'attribute';
    }

    private function handleTextFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;
        if (! $value) {
            return $query;
        }

        // Handle new structure with text and mode
        if (is_array($value) && isset($value['text'])) {
            $text = $value['text'];
            $mode = $value['mode'] ?? 'contains';

            // Use join instead of whereHas for better performance
            return $this->applyTextFilterWithJoin($query, $attribute, $text, $mode);
        }

        // Original structure
        $operator = $filter['operator'] ?? '=';

        return $this->applyTextFilterWithJoin($query, $attribute, $value, $operator === 'like' ? 'contains' : 'exact', $operator);
    }

    /**
     * Apply text filter using join for better performance
     */
    private function applyTextFilterWithJoin(Builder $query, Attribute $attribute, string $text, string $mode, string $operator = '='): Builder
    {
        if ($operator === '!=') {
            // For != operator, we need a special approach to handle inequality
            return $query->where(function ($query) use ($attribute, $text) {
                // Include job posts that don't have this attribute at all
                $query->whereNotExists(function ($q) use ($attribute) {
                    $q->select(DB::raw(1))
                        ->from('job_attribute_values')
                        ->whereRaw('job_attribute_values.job_post_id = job_posts.id')
                        ->where('job_attribute_values.attribute_id', '=', $attribute->id);
                })
                // Or job posts that have this attribute but with a different value
                    ->orWhereExists(function ($q) use ($attribute, $text) {
                        $q->select(DB::raw(1))
                            ->from('job_attribute_values')
                            ->whereRaw('job_attribute_values.job_post_id = job_posts.id')
                            ->where('job_attribute_values.attribute_id', '=', $attribute->id)
                            ->where('job_attribute_values.value', '!=', $text);
                    });
            });
        }

        // Use a more efficient join approach instead of whereHas
        $query->join('job_attribute_values AS text_filter', function ($join) use ($attribute, $text, $mode, $operator) {
            $join->on('job_posts.id', '=', 'text_filter.job_post_id')
                ->where('text_filter.attribute_id', '=', $attribute->id);

            // Apply different search modes
            switch ($mode) {
                case 'contains':
                    $join->where('text_filter.value', 'LIKE', "%{$text}%");
                    break;
                case 'starts_with':
                    $join->where('text_filter.value', 'LIKE', "{$text}%");
                    break;
                case 'ends_with':
                    $join->where('text_filter.value', 'LIKE', "%{$text}");
                    break;
                case 'exact':
                    $join->where('text_filter.value', '=', $text);
                    break;
                case 'not_contains':
                    $join->where(function ($q) use ($text) {
                        $q->where('text_filter.value', 'NOT LIKE', "%{$text}%")
                            ->orWhereNull('text_filter.value');
                    });
                    break;
                default:
                    if ($operator === 'like') {
                        $join->where('text_filter.value', 'LIKE', "%{$text}%");
                    } else {
                        $join->where('text_filter.value', $operator, $text);
                    }
            }
        });

        // Ensure we select distinct job posts to avoid duplicates from the join
        return $query->distinct('job_posts.id');
    }

    private function handleNumberFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;
        if (! $value) {
            return $query;
        }

        // Define the subquery approach - more reliable for numeric comparisons
        $subquery = function ($operator, $numericValue) use ($attribute) {
            return function ($query) use ($attribute, $operator, $numericValue) {
                $query->select('job_post_id')
                    ->from('job_attribute_values')
                    ->where('attribute_id', $attribute->id)
                    ->whereRaw("CAST(value AS DECIMAL(10,2)) $operator ?", [(float) $numericValue]);
            };
        };

        // Check if the value is an array with min and/or max for range filter
        if (is_array($value)) {
            $min = $value['min'] ?? null;
            $max = $value['max'] ?? null;

            if (isset($min) || isset($max)) {
                // Clear any existing joins to avoid conflicts
                $cleanQuery = clone $query;

                // Use a subquery approach for better reliability with numeric comparisons
                if (isset($min) && is_numeric($min)) {
                    $cleanQuery->whereIn('job_posts.id', $subquery('>=', $min));
                }

                if (isset($max) && is_numeric($max)) {
                    $cleanQuery->whereIn('job_posts.id', $subquery('<=', $max));
                }

                return $cleanQuery;
            }
        }

        $operator = $filter['operator'] ?? '=';

        // Clear any existing joins to avoid conflicts
        $cleanQuery = clone $query;

        // Use subquery for explicit operator comparisons
        if (in_array($operator, ['>', '<', '>=', '<='])) {
            return $cleanQuery->whereIn('job_posts.id', $subquery($operator, $value));
        } else {
            // For equality or other operators
            return $cleanQuery->whereIn('job_posts.id', function ($query) use ($attribute, $operator, $value) {
                $query->select('job_post_id')
                    ->from('job_attribute_values')
                    ->where('attribute_id', $attribute->id)
                    ->where('value', $operator, (string) $value);
            });
        }
    }

    private function handleBooleanFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;

        if (is_null($value)) {
            return $query;
        }

        $operator = $filter['operator'] ?? '=';

        // Convert to boolean if it's not already
        if (is_string($value)) {
            $value = strtolower($value);
            $boolValue = $value === 'true' || $value === '1' || $value === 'yes';
        } else {
            $boolValue = (bool) $value;
        }

        // Use join for better performance
        $query->join('job_attribute_values AS bool_filter', function ($join) use ($attribute, $boolValue, $operator) {
            $join->on('job_posts.id', '=', 'bool_filter.job_post_id')
                ->where('bool_filter.attribute_id', '=', $attribute->id);

            if ($boolValue) {
                // If looking for true, match any of these true values
                if ($operator === '=') {
                    $join->whereIn('bool_filter.value', ['true', '1', 'yes']);
                } else {
                    $join->whereNotIn('bool_filter.value', ['true', '1', 'yes']);
                }
            } else {
                // If looking for false, match any of these false values
                if ($operator === '=') {
                    $join->whereIn('bool_filter.value', ['false', '0', 'no']);
                } else {
                    $join->whereNotIn('bool_filter.value', ['false', '0', 'no']);
                }
            }
        });

        // Ensure we select distinct job posts to avoid duplicates from the join
        return $query->distinct('job_posts.id');
    }

    private function handleSelectFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;
        if (! $value) {
            return $query;
        }

        $operator = $filter['operator'] ?? '=';

        // New structure: value is an array with values and mode
        if (is_array($value) && isset($value['values'])) {
            $selectValues = $value['values'];
            $mode = $value['mode'] ?? 'any';

            // Ensure selectValues is always an array
            if (! is_array($selectValues)) {
                $selectValues = [$selectValues];
            }
        } else {
            // Original structure
            $mode = $filter['mode'] ?? 'any';
            $selectValues = is_array($value) ? $value : [$value];
        }

        // Convert values to lowercase for case-insensitive comparison
        $normalizedValues = [];
        foreach ($selectValues as $val) {
            if (is_string($val)) {
                $normalizedValues[] = strtolower($val);
            } else {
                $normalizedValues[] = $val;
            }
        }

        // Ensure options is an array before mapping
        $options = $attribute->options ?? [];
        if (is_string($options)) {
            try {
                $options = json_decode($options, true) ?? [];
            } catch (\Exception $e) {
                $options = [];
            }
        }

        $validOptions = [];
        if (is_array($options)) {
            foreach ($options as $opt) {
                $validOptions[] = is_string($opt) ? strtolower($opt) : $opt;
            }
        }

        // Filter out invalid options
        $validValues = [];
        foreach ($normalizedValues as $val) {
            $strVal = is_string($val) ? $val : (string) $val;
            if (in_array($strVal, $validOptions)) {
                $validValues[] = $strVal;
            }
        }

        if (empty($validValues)) {
            return $query->whereRaw('1 = 0'); // Force empty result set for invalid values
        }

        // Special handling for inequality
        if ($operator === '!=') {
            // For inequality, check all jobs that don't have this attribute value
            $exactValue = $validValues[0];

            // Using subquery approach for better performance with NOT EXISTS
            $query->where(function ($query) use ($attribute, $exactValue) {
                $query->whereNotExists(function ($subquery) use ($attribute, $exactValue) {
                    $subquery->select(DB::raw(1))
                        ->from('job_attribute_values')
                        ->whereRaw('job_attribute_values.job_post_id = job_posts.id')
                        ->where('job_attribute_values.attribute_id', $attribute->id)
                        ->where(function ($sq) use ($exactValue) {
                            $sq->whereRaw('LOWER(job_attribute_values.value) = ?', [strtolower($exactValue)])
                                ->orWhere('job_attribute_values.value', 'LIKE', '%"'.strtolower($exactValue).'"%');
                        });
                });
            });

            return $query;
        }

        if ($mode === 'all') {
            // For ALL mode - for each value, find job posts that have that value
            foreach ($validValues as $value) {
                $query->whereExists(function ($subquery) use ($attribute, $value) {
                    $subquery->select(DB::raw(1))
                        ->from('job_attribute_values')
                        ->whereRaw('job_attribute_values.job_post_id = job_posts.id')
                        ->where('job_attribute_values.attribute_id', $attribute->id)
                        ->where(function ($q) use ($value) {
                            $q->where('job_attribute_values.value', $value)
                                ->orWhere('job_attribute_values.value', 'LIKE', '%"'.$value.'"%')
                                ->orWhere('job_attribute_values.value', 'LIKE', '%'.$value.'%');
                        });
                });
            }

            return $query;
        }

        if ($mode === 'any' || $operator === 'in') {
            // Handle special java case
            if (count($validValues) === 1 && $validValues[0] === 'java') {
                // Using optimized join for better performance
                $query->join('job_attribute_values AS java_filter', function ($join) use ($attribute) {
                    $join->on('job_posts.id', '=', 'java_filter.job_post_id')
                        ->where('java_filter.attribute_id', '=', $attribute->id)
                        ->where(function ($q) {
                            // Need a json array with "java"
                            $q->where('java_filter.value', 'LIKE', '%"java"%');

                            // But NOT "javascript"
                            $q->where(function ($inner) {
                                $inner->where('java_filter.value', 'NOT LIKE', '%javascript%')
                                    // Or if it does contain javascript, then it needs to also have java as a separate value
                                    ->orWhere('java_filter.value', 'LIKE', '%"java"%');
                            });
                        });
                });

                return $query->distinct('job_posts.id');
            }

            // Using optimized join for better performance
            $query->join('job_attribute_values AS select_any_filter', function ($join) use ($attribute, $validValues) {
                $join->on('job_posts.id', '=', 'select_any_filter.job_post_id')
                    ->where('select_any_filter.attribute_id', '=', $attribute->id)
                    ->where(function ($q) use ($validValues) {
                        foreach ($validValues as $value) {
                            $q->orWhere('select_any_filter.value', $value)
                                ->orWhere('select_any_filter.value', 'LIKE', '%"'.$value.'"%')
                                ->orWhere('select_any_filter.value', 'LIKE', '%'.$value.'%');
                        }
                    });
            });

            return $query->distinct('job_posts.id');
        }

        if ($mode === 'none' || $operator === 'not_in') {
            // Using subquery approach for better performance with NOT EXISTS
            $query->whereNotExists(function ($subquery) use ($attribute, $validValues) {
                $subquery->select(DB::raw(1))
                    ->from('job_attribute_values')
                    ->whereRaw('job_attribute_values.job_post_id = job_posts.id')
                    ->where('job_attribute_values.attribute_id', $attribute->id)
                    ->where(function ($q) use ($validValues) {
                        foreach ($validValues as $value) {
                            $q->orWhere('job_attribute_values.value', $value)
                                ->orWhere('job_attribute_values.value', 'LIKE', '%"'.$value.'"%')
                                ->orWhere('job_attribute_values.value', 'LIKE', '%'.$value.'%');
                        }
                    });
            });

            return $query;
        }

        // Default equality match with optimized join
        $query->join('job_attribute_values AS select_equal_filter', function ($join) use ($attribute, $validValues) {
            $join->on('job_posts.id', '=', 'select_equal_filter.job_post_id')
                ->where('select_equal_filter.attribute_id', '=', $attribute->id)
                ->where(function ($q) use ($validValues) {
                    $value = $validValues[0];
                    $q->where('select_equal_filter.value', '=', $value)
                        ->orWhere('select_equal_filter.value', 'LIKE', '%"'.$value.'"%')
                        ->orWhere('select_equal_filter.value', 'LIKE', '%'.$value.'%');
                });
        });

        return $query->distinct('job_posts.id');
    }

    private function handleDateFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;
        if (! $value) {
            return $query;
        }

        // Check if this is a range query
        if (is_array($value)) {
            // Handle date range with from/to or after/before format
            $from = $value['from'] ?? $value['after'] ?? null;
            $to = $value['to'] ?? $value['before'] ?? null;

            // Use join for better performance with date ranges
            $query->join('job_attribute_values AS date_range_filter', function ($join) use ($attribute, $from, $to) {
                $join->on('job_posts.id', '=', 'date_range_filter.job_post_id')
                    ->where('date_range_filter.attribute_id', '=', $attribute->id);

                if ($from) {
                    try {
                        $fromDate = new \DateTime($from);
                        $join->whereDate('date_range_filter.value', '>=', $fromDate->format('Y-m-d'));
                    } catch (\Exception $e) {
                        // Invalid date format, skip this condition
                    }
                }

                if ($to) {
                    try {
                        $toDate = new \DateTime($to);
                        $join->whereDate('date_range_filter.value', '<=', $toDate->format('Y-m-d'));
                    } catch (\Exception $e) {
                        // Invalid date format, skip this condition
                    }
                }
            });

            return $query->distinct('job_posts.id');
        }

        // Check if this is a relative date term
        if (is_string($value) && in_array($value, ['today', 'yesterday', 'tomorrow', 'this_week', 'last_week', 'next_week', 'this_month', 'last_month', 'next_month'])) {
            return $this->handleRelativeDateFilter($query, $attribute, $value);
        }

        $operator = $filter['operator'] ?? '=';

        try {
            // Parse and normalize the date value
            $dateValue = new \DateTime($value);

            // Use join for better performance
            $query->join('job_attribute_values AS date_filter', function ($join) use ($attribute, $operator, $dateValue) {
                $join->on('job_posts.id', '=', 'date_filter.job_post_id')
                    ->where('date_filter.attribute_id', '=', $attribute->id)
                    ->whereDate('date_filter.value', $operator, $dateValue->format('Y-m-d'));
            });

            return $query->distinct('job_posts.id');
        } catch (\Exception $e) {
            // If date parsing fails, return empty result set
            return $query->whereRaw('1 = 0');
        }
    }

    private function handleRelativeDateFilter(Builder $query, Attribute $attribute, string $term): Builder
    {
        $now = new \DateTime;
        $start = null;
        $end = null;

        switch ($term) {
            case 'today':
                $start = (clone $now)->setTime(0, 0, 0);
                $end = (clone $now)->setTime(23, 59, 59);
                break;

            case 'yesterday':
                $start = (clone $now)->modify('-1 day')->setTime(0, 0, 0);
                $end = (clone $now)->modify('-1 day')->setTime(23, 59, 59);
                break;

            case 'tomorrow':
                $start = (clone $now)->modify('+1 day')->setTime(0, 0, 0);
                $end = (clone $now)->modify('+1 day')->setTime(23, 59, 59);
                break;

            case 'this_week':
                $start = (clone $now)->modify('this week monday')->setTime(0, 0, 0);
                $end = (clone $now)->modify('this week sunday')->setTime(23, 59, 59);
                break;

            case 'last_week':
                $start = (clone $now)->modify('last week monday')->setTime(0, 0, 0);
                $end = (clone $now)->modify('last week sunday')->setTime(23, 59, 59);
                break;

            case 'next_week':
                $start = (clone $now)->modify('next week monday')->setTime(0, 0, 0);
                $end = (clone $now)->modify('next week sunday')->setTime(23, 59, 59);
                break;

            case 'this_month':
                $start = (clone $now)->modify('first day of this month')->setTime(0, 0, 0);
                $end = (clone $now)->modify('last day of this month')->setTime(23, 59, 59);
                break;

            case 'last_month':
                $start = (clone $now)->modify('first day of last month')->setTime(0, 0, 0);
                $end = (clone $now)->modify('last day of last month')->setTime(23, 59, 59);
                break;

            case 'next_month':
                $start = (clone $now)->modify('first day of next month')->setTime(0, 0, 0);
                $end = (clone $now)->modify('last day of next month')->setTime(23, 59, 59);
                break;
        }

        if ($start && $end) {
            // Use join for better performance
            $query->join('job_attribute_values AS relative_date_filter', function ($join) use ($attribute, $start, $end) {
                $join->on('job_posts.id', '=', 'relative_date_filter.job_post_id')
                    ->where('relative_date_filter.attribute_id', '=', $attribute->id)
                    ->whereDate('relative_date_filter.value', '>=', $start->format('Y-m-d'))
                    ->whereDate('relative_date_filter.value', '<=', $end->format('Y-m-d'));
            });

            return $query->distinct('job_posts.id');
        }

        return $query;
    }
}
