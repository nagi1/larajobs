<?php

namespace App\Filters;

use App\Enums\AttributeType;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Builder;

class EavFilter
{
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

        $attribute = Attribute::query()
            ->where('name', $attributeName)
            ->whereIn('type', [AttributeType::TEXT, AttributeType::NUMBER, AttributeType::BOOLEAN, AttributeType::SELECT, AttributeType::DATE])
            ->first();

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

    private function handleTextFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;
        if (! $value) {
            return $query;
        }

        $operator = $filter['operator'] ?? '=';

        return $query->whereHas('jobAttributeValues', function ($query) use ($attribute, $operator, $value) {
            $query->where('attribute_id', $attribute->id)
                ->when($operator === 'like', function ($query) use ($value) {
                    $query->where('value', 'like', "%{$value}%");
                }, function ($query) use ($operator, $value) {
                    $query->where('value', $operator, $value);
                });
        });
    }

    private function handleNumberFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;
        if (! $value) {
            return $query;
        }

        $operator = $filter['operator'] ?? '=';

        return $query->whereHas('jobAttributeValues', function (Builder $query) use ($attribute, $operator, $value) {
            $query->where('attribute_id', $attribute->id)
                ->where('value', $operator, $value);
        });
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

        // In the database, boolean values are stored as strings
        return $query->whereHas('jobAttributeValues', function (Builder $query) use ($attribute, $boolValue, $operator) {
            $query->where('attribute_id', $attribute->id);

            if ($boolValue) {
                // If looking for true, match any of these true values
                if ($operator === '=') {
                    $query->whereIn('value', ['true', '1', 'yes']);
                } else {
                    $query->whereNotIn('value', ['true', '1', 'yes']);
                }
            } else {
                // If looking for false, match any of these false values
                if ($operator === '=') {
                    $query->whereIn('value', ['false', '0', 'no']);
                } else {
                    $query->whereNotIn('value', ['false', '0', 'no']);
                }
            }
        });
    }

    private function handleSelectFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;
        if (! $value) {
            return $query;
        }

        $operator = $filter['operator'] ?? '=';
        $values = is_array($value) ? $value : [$value];

        // Convert values to lowercase for case-insensitive comparison
        $values = array_map('strtolower', $values);

        // Ensure options is an array before mapping
        $options = $attribute->options ?? [];
        if (is_string($options)) {
            try {
                $options = json_decode($options, true) ?? [];
            } catch (\Exception $e) {
                $options = [];
            }
        }

        $validOptions = is_array($options) ? array_map('strtolower', $options) : [];

        // Filter out invalid options
        $values = array_values(array_filter($values, fn ($v) => in_array($v, $validOptions)));

        if (empty($values)) {
            return $query->whereRaw('1 = 0'); // Force empty result set for invalid values
        }

        return $query->whereHas('jobAttributeValues', function ($query) use ($attribute, $values, $operator) {
            $query->where('attribute_id', $attribute->id);

            if ($operator === 'in' && count($values) > 1) {
                $query->where(function ($q) use ($values) {
                    foreach ($values as $value) {
                        $q->orWhereRaw('LOWER(value) = ?', [strtolower($value)]);
                    }
                });
            } elseif ($operator === '!=') {
                $query->whereRaw('LOWER(value) != ?', [strtolower($values[0])]);
            } else {
                $query->whereRaw('LOWER(value) = ?', [strtolower($values[0])]);
            }
        });
    }

    private function handleDateFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;
        if (! $value) {
            return $query;
        }

        $operator = $filter['operator'] ?? '=';

        try {
            // Parse and normalize the date value
            $dateValue = new \DateTime($value);

            return $query->whereHas('jobAttributeValues', function ($query) use ($attribute, $operator, $dateValue) {
                $query->where('attribute_id', $attribute->id)
                    ->whereDate('value', $operator, $dateValue->format('Y-m-d'));
            });
        } catch (\Exception $e) {
            // If date parsing fails, return empty result set
            return $query->whereRaw('1 = 0');
        }
    }
}
