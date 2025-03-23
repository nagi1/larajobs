<?php

namespace App\Services;

use App\Filters\CategoryFilter;
use App\Filters\EavFilter;
use App\Filters\IsRemoteFilter;
use App\Filters\JobTypeFilter;
use App\Filters\LanguageFilter;
use App\Filters\LocationFilter;
use App\Filters\StatusFilter;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class JobFilterService
{
    private array $filters = [
        'status' => StatusFilter::class,
        'job_type' => JobTypeFilter::class,
        'is_remote' => IsRemoteFilter::class,
        'languages' => LanguageFilter::class,
        'locations' => LocationFilter::class,
        'categories' => CategoryFilter::class,
    ];

    public function apply(Builder $query, string $filterString): Builder
    {
        if (empty($filterString)) {
            return $query;
        }

        $conditions = $this->parseFilterString($filterString);

        return $this->applyConditions($query, $conditions);
    }

    private function parseFilterString(string $filterString): array
    {
        $filterString = trim($filterString, '()');
        $conditions = [];
        $currentGroup = [];
        $currentOperator = 'and';

        $parts = preg_split('/\s+OR\s+/', $filterString);

        foreach ($parts as $part) {
            $innerConditions = [];
            $innerParts = preg_split('/\s+AND\s+/', trim($part, '()'));

            foreach ($innerParts as $innerPart) {
                $condition = $this->parseCondition(trim($innerPart));
                if ($condition) {
                    $innerConditions[] = $condition;
                }
            }

            if (! empty($innerConditions)) {
                $conditions[] = [
                    'operator' => $currentOperator,
                    'conditions' => $innerConditions,
                ];
                $currentOperator = 'or';
            }
        }

        return $conditions;
    }

    private function parseCondition(string $condition): ?array
    {
        // Handle EAV attributes
        if (Str::startsWith($condition, 'attribute:')) {
            return $this->parseEavCondition($condition);
        }

        // Handle relationship conditions
        if (preg_match('/^(languages|locations|categories)\s+(HAS_ANY|IS_ANY|EXISTS)\s*(?:\((.*)\))?$/', $condition, $matches)) {
            return [
                'type' => 'relationship',
                'relation' => $matches[1],
                'mode' => strtolower($matches[2]),
                'values' => isset($matches[3]) ? array_map('trim', explode(',', $matches[3])) : null,
            ];
        }

        // Handle standard conditions
        if (preg_match('/^([a-zA-Z_]+)\s*([=!<>]+)\s*(.+)$/', $condition, $matches)) {
            return [
                'type' => 'standard',
                'field' => $matches[1],
                'operator' => $matches[2],
                'value' => $this->parseValue($matches[3]),
            ];
        }

        return null;
    }

    private function parseEavCondition(string $condition): array
    {
        $parts = explode(':', $condition, 2);
        $attributeName = $parts[1];

        if (preg_match('/^([a-zA-Z_]+)\s*([=!<>]+)\s*(.+)$/', $attributeName, $matches)) {
            return [
                'type' => 'eav',
                'name' => $matches[1],
                'operator' => $matches[2],
                'value' => $this->parseValue($matches[3]),
            ];
        }

        return [];
    }

    private function parseValue(string $value): mixed
    {
        // Remove quotes if they exist
        $value = trim($value, '"\'');

        // Handle boolean values
        if (in_array(strtolower($value), ['true', 'false'])) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        // Handle numeric values
        if (is_numeric($value)) {
            return floatval($value);
        }

        return $value;
    }

    private function applyConditions(Builder $query, array $conditions): Builder
    {
        $firstGroup = true;

        foreach ($conditions as $group) {
            $operator = $group['operator'];
            $groupConditions = $group['conditions'];

            $method = $firstGroup ? 'where' : 'orWhere';
            $firstGroup = false;

            $query->$method(function ($subQuery) use ($groupConditions) {
                $firstCondition = true;
                foreach ($groupConditions as $condition) {
                    if (isset($condition['type']) && $condition['type'] === 'group') {
                        $method = $firstCondition ? 'where' : 'where';
                        $subQuery->$method(function ($nestedQuery) use ($condition) {
                            foreach ($condition['conditions'] as $nestedCondition) {
                                $this->applyCondition($nestedQuery, $nestedCondition);
                            }
                        });
                    } else {
                        $method = $firstCondition ? 'where' : 'where';
                        $this->applyCondition($subQuery, $condition);
                    }
                    $firstCondition = false;
                }
            });
        }

        return $query;
    }

    private function applyCondition(Builder $query, array $condition): void
    {
        switch ($condition['type']) {
            case 'standard':
                if (isset($this->filters[$condition['field']])) {
                    $filter = new $this->filters[$condition['field']];
                    $filter->apply($query, $condition['value']);
                } else {
                    $query->where($condition['field'], $condition['operator'], $condition['value']);
                }
                break;
            case 'eav':
                $attribute = Attribute::where('name', $condition['name'])->first();
                if (! $attribute) {
                    return;
                }

                $filter = new EavFilter;
                $filter->apply($query, [
                    'name' => $condition['name'],
                    'operator' => $condition['operator'],
                    'value' => $condition['value'],
                ]);
                break;
            case 'relationship':
                $relation = $condition['relation'];
                if (! isset($this->filters[$relation])) {
                    return;
                }

                $filter = new $this->filters[$relation];
                $query->where(function ($subQuery) use ($filter, $condition) {
                    $filter->apply($subQuery, [
                        'mode' => $condition['mode'],
                        'values' => $condition['values'],
                    ]);
                });
                break;
        }
    }
}
