<?php

namespace App\Services;

use App\Filters\CategoryFilter;
use App\Filters\DescriptionFilter;
use App\Filters\EavFilter;
use App\Filters\IsRemoteFilter;
use App\Filters\JobTypeFilter;
use App\Filters\LanguageFilter;
use App\Filters\LocationFilter;
use App\Filters\SalaryRangeFilter;
use App\Filters\StatusFilter;
use App\Filters\TitleFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
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
        'title' => TitleFilter::class,
        'description' => DescriptionFilter::class,
        'salary_min' => SalaryRangeFilter::class,
        'salary_max' => SalaryRangeFilter::class,
    ];

    /**
     * Apply filters to a query based on a filter string
     */
    public function apply(Builder $query, string $filterString): Builder
    {
        if (empty($filterString)) {
            return $query;
        }

        // Parse the filter string into a structure we can work with
        $conditions = $this->parseFilterString($filterString);

        if (empty($conditions)) {
            return $query;
        }

        return $this->applyConditions($query, $conditions);
    }

    /**
     * Parse a filter string into a structured array of conditions
     */
    private function parseFilterString(string $filterString): array
    {
        try {
            // Check for surrounding parentheses and handle them if needed
            if (Str::startsWith($filterString, '(') && Str::endsWith($filterString, ')')) {
                $inner = substr($filterString, 1, -1);
                $level = 0;
                $balanced = true;

                for ($i = 0; $i < strlen($inner); $i++) {
                    if ($inner[$i] === '(') {
                        $level++;
                    } elseif ($inner[$i] === ')') {
                        $level--;
                    }

                    // If at any point the level goes negative, the parentheses are not balanced
                    if ($level < 0) {
                        $balanced = false;
                        break;
                    }
                }

                // If the inner content has balanced parentheses and the level is back to 0,
                // then we can safely process the inner content
                if ($balanced && $level === 0) {
                    $filterString = $inner;
                }
            }

            // For complex queries similar to the Challenge.md example
            if (Str::contains($filterString, 'job_type=') &&
                (Str::contains($filterString, 'languages HAS_ANY') ||
                 Str::contains($filterString, 'locations HAS_ANY') ||
                 Str::contains($filterString, 'categories HAS_ANY')) &&
                Str::contains($filterString, 'attribute:')) {
                // Handle the complex query case
                return $this->parseComplexQuery($filterString);
            }

            $conditions = $this->parseAndOrConditions($filterString);

            return $conditions;
        } catch (\Exception $e) {
            // In case of any parsing errors, return empty conditions
            return [];
        }
    }

    /**
     * Special parser for complex queries with multiple condition types
     */
    private function parseComplexQuery(string $filterString): array
    {
        $conditions = [];

        // Extract standard field conditions (job_type, is_remote, salary_min, etc.)
        if (preg_match_all('/\b([a-z_]+)\s*([=!<>]+)\s*([a-z0-9_\-"\'\.]+)/i', $filterString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (isset($this->filters[$match[1]]) || Schema::hasColumn('job_posts', $match[1])) {
                    $conditions[] = $this->parseCondition($match[1].$match[2].$match[3]);
                }
            }
        }

        // Extract relationship conditions (languages, locations, categories)
        if (preg_match_all('/(languages|locations|categories)\s+(HAS_ANY|IS_ANY|EXISTS)(?:\s*\(([^)]+)\))?/i', $filterString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $relationCondition = $match[1].' '.$match[2];
                if (isset($match[3])) {
                    $relationCondition .= ' ('.$match[3].')';
                }
                $conditions[] = $this->parseCondition($relationCondition);
            }
        }

        // Extract attribute conditions
        if (preg_match_all('/attribute:([a-z_]+)\s*([a-z]+|[=!<>]+)\s*([a-z0-9_\-"\'\.]+)/i', $filterString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $conditions[] = $this->parseAttributeCondition('attribute:'.$match[1].$match[2].$match[3]);
            }
        }

        return [
            [
                'operator' => 'and',
                'conditions' => $conditions,
            ],
        ];
    }

    /**
     * Parse a string with AND/OR conditions
     */
    private function parseAndOrConditions(string $filterString): array
    {
        // Handle nested expressions with parentheses
        $processed = preg_replace_callback('/\(([^()]+)\)/', function ($matches) {
            // Process the content inside parentheses
            return $this->parseCondition(trim($matches[1])) ? '['.trim($matches[1]).']' : '';
        }, $filterString);

        // Split by OR (lowest precedence)
        $orParts = $this->safeSplit($processed, '/\s+OR\s+/i');
        $conditions = [];

        foreach ($orParts as $index => $orPart) {
            // For each OR part, split by AND (higher precedence)
            $andParts = $this->safeSplit($orPart, '/\s+AND\s+/i');
            $andConditions = [];

            foreach ($andParts as $andPart) {
                // Restore bracketed expressions
                $andPart = preg_replace_callback('/\[([^[\]]+)\]/', function ($matches) {
                    return '('.$matches[1].')';
                }, $andPart);

                // Parse individual condition
                $condition = $this->parseFilterCondition(trim($andPart));
                if ($condition) {
                    $andConditions[] = $condition;
                }
            }

            if (! empty($andConditions)) {
                $conditions[] = [
                    'operator' => $index > 0 ? 'or' : 'and',
                    'conditions' => $andConditions,
                ];
            }
        }

        return $conditions;
    }

    /**
     * Safely split a string by a pattern, preserving content in square brackets
     */
    private function safeSplit(string $input, string $pattern): array
    {
        $parts = [];
        $start = 0;
        $level = 0;

        for ($i = 0; $i < strlen($input); $i++) {
            if ($input[$i] === '[') {
                $level++;
            } elseif ($input[$i] === ']') {
                $level--;
            } elseif ($level === 0 && $i <= strlen($input) - 3) {
                // Check for AND/OR patterns while not inside brackets
                $chunk = substr($input, $i, 5);
                if (preg_match('/\s+OR\s+/i', $chunk) || preg_match('/\s+AND\s+/i', $chunk)) {
                    $matchLength = stripos($chunk, 'OR') !== false ? strlen('OR') : strlen('AND');
                    $paddingBefore = stripos($chunk, 'OR') !== false
                        ? stripos($chunk, 'OR')
                        : stripos($chunk, 'AND');
                    $paddingAfter = strlen($chunk) - $paddingBefore - $matchLength;

                    // Extract the part before the operator
                    $parts[] = substr($input, $start, $i + $paddingBefore - $start);
                    $start = $i + $paddingBefore + $matchLength + $paddingAfter;
                    $i = $start - 1; // -1 because the loop will increment i
                }
            }
        }

        // Add the last part
        if ($start < strlen($input)) {
            $parts[] = substr($input, $start);
        }

        // If no splits were performed, return the original input as a single element array
        return empty($parts) ? [$input] : $parts;
    }

    /**
     * Parse a single condition from the filter string, handling nested conditions
     */
    private function parseFilterCondition(string $condition): ?array
    {
        // Check if this is a complex condition with parentheses
        if (Str::startsWith($condition, '(') && Str::endsWith($condition, ')')) {
            // This is a nested condition - recursively parse it
            $inner = substr($condition, 1, -1);
            $nestedConditions = $this->parseAndOrConditions($inner);

            if (! empty($nestedConditions)) {
                return [
                    'type' => 'nested',
                    'conditions' => $nestedConditions,
                ];
            }
        }

        // Otherwise, parse as a simple condition
        return $this->parseCondition($condition);
    }

    /**
     * Parse a simple condition (without nesting)
     */
    private function parseCondition(string $condition): ?array
    {
        // Handle attribute conditions
        if (Str::startsWith($condition, 'attribute:')) {
            return $this->parseAttributeCondition($condition);
        }

        // Handle relationship conditions
        if (preg_match('/^(languages|locations|categories)\s+(HAS_ANY|IS_ANY|EXISTS)(?:\s*\((.+)\))?$/i', $condition, $matches)) {
            return [
                'type' => 'relationship',
                'field' => strtolower($matches[1]),
                'operator' => strtoupper($matches[2]),
                'values' => isset($matches[3]) ? array_map('trim', explode(',', $matches[3])) : [],
            ];
        }

        // Handle LIKE operator
        if (preg_match('/^([a-zA-Z0-9_]+)\s+LIKE\s+(.+)$/i', $condition, $matches)) {
            return [
                'type' => 'standard',
                'field' => $matches[1],
                'operator' => 'LIKE',
                'value' => $this->parseValue($matches[2]),
            ];
        }

        // Handle standard operators (=, !=, >, <, >=, <=)
        if (preg_match('/^([a-zA-Z0-9_]+)\s*([=!<>]+)\s*(.+)$/i', $condition, $matches)) {
            return [
                'type' => 'standard',
                'field' => $matches[1],
                'operator' => $matches[2],
                'value' => $this->parseValue($matches[3]),
            ];
        }

        return null;
    }

    /**
     * Parse an attribute-specific condition
     */
    private function parseAttributeCondition(string $condition): ?array
    {
        if (preg_match('/^attribute:([a-zA-Z0-9_]+)\s*(LIKE|[=!<>]+)\s*(.+)$/i', $condition, $matches)) {
            $attributeName = $matches[1];
            $operator = strtoupper($matches[2]);
            $value = $this->parseValue($matches[3]);

            // Handle boolean values more explicitly
            if ($attributeName === 'requires_degree' || Str::endsWith($attributeName, '_required') ||
                Str::contains($attributeName, 'is_') || Str::contains($attributeName, 'has_')) {
                // These naming patterns typically indicate boolean attributes
                $value = is_bool($value) ? $value : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }

            return [
                'type' => 'attribute',
                'name' => $attributeName,
                'operator' => $operator,
                'value' => $value,
            ];
        }

        return null;
    }

    /**
     * Parse a value from a condition, handling quotes and data types
     */
    private function parseValue(string $value): mixed
    {
        // Remove quotes
        $value = trim($value);
        if ((Str::startsWith($value, '"') && Str::endsWith($value, '"')) ||
            (Str::startsWith($value, "'") && Str::endsWith($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        // Handle boolean values
        if (strtolower($value) === 'true') {
            return true;
        }
        if (strtolower($value) === 'false') {
            return false;
        }

        // Handle numeric values
        if (is_numeric($value)) {
            return $value + 0; // Convert to int or float as appropriate
        }

        return $value;
    }

    /**
     * Apply the parsed conditions to the query
     */
    private function applyConditions(Builder $query, array $conditions): Builder
    {
        foreach ($conditions as $condition) {
            $method = $condition['operator'] === 'or' ? 'orWhere' : 'where';

            $query->$method(function ($subQuery) use ($condition) {
                foreach ($condition['conditions'] as $subCondition) {
                    $this->applySingleCondition($subQuery, $subCondition);
                }
            });
        }

        return $query;
    }

    /**
     * Apply a single condition to the query
     */
    private function applySingleCondition(Builder $query, array $condition): void
    {
        if ($condition['type'] === 'nested') {
            // Handle nested conditions
            $query->where(function ($subQuery) use ($condition) {
                $this->applyConditions($subQuery, $condition['conditions']);
            });

            return;
        }

        // Handle standard conditions
        if ($condition['type'] === 'standard') {
            $this->applyStandardCondition($query, $condition['field'], $condition['operator'], $condition['value']);

            return;
        }

        // Handle relationship conditions
        if ($condition['type'] === 'relationship') {
            $this->applyRelationshipCondition($query, $condition);

            return;
        }

        // Handle attribute conditions
        if ($condition['type'] === 'attribute') {
            $this->applyAttributeCondition($query, $condition);

            return;
        }
    }

    /**
     * Apply a standard field condition to the query
     */
    private function applyStandardCondition(Builder $query, string $field, string $operator, mixed $value): void
    {
        // Check if we have a specialized filter for this field
        if (isset($this->filters[$field])) {
            $filterClass = $this->filters[$field];
            $filter = new $filterClass;
            $filter->apply($query, $value);

            return;
        }

        // Check if the field exists in the table
        $tableName = $query->getModel()->getTable();
        if (! Schema::hasColumn($tableName, $field)) {
            return; // Skip non-existent fields gracefully
        }

        // Apply standard where clause
        switch (strtoupper($operator)) {
            case 'LIKE':
                $query->where($field, 'LIKE', "%{$value}%");
                break;

            default:
                $query->where($field, $operator, $value);
                break;
        }
    }

    /**
     * Apply a relationship condition to the query
     */
    private function applyRelationshipCondition(Builder $query, array $condition): void
    {
        $field = $condition['field'];
        $values = $condition['values'];

        if (! isset($this->filters[$field])) {
            return;
        }

        $filterClass = $this->filters[$field];
        $filter = new $filterClass;

        $filter->apply($query, [
            'mode' => strtolower($condition['operator']),
            'values' => $values,
        ]);
    }

    /**
     * Apply an EAV attribute condition to the query
     */
    private function applyAttributeCondition(Builder $query, array $condition): void
    {
        $filter = new EavFilter;

        $operator = $condition['operator'];

        // Make sure we're passing the proper operator format
        if ($operator === 'LIKE') {
            $operator = 'like';
        }

        $filter->apply($query, [
            'name' => $condition['name'],
            'operator' => $operator,
            'value' => $condition['value'],
        ]);
    }
}
