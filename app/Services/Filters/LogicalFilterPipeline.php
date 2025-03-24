<?php

namespace App\Services\Filters;

use App\Contracts\Filters\PipelineInterface;
use App\Exceptions\FilterException;
use Illuminate\Database\Eloquent\Builder;

class LogicalFilterPipeline extends FilterPipeline implements PipelineInterface
{
    /**
     * Process filters on the query, with support for logical operators.
     *
     * Format for filters:
     * - Standard filter: ['field_name' => 'value']
     * - AND condition: ['and' => [['field1' => 'value1'], ['field2' => 'value2']]]
     * - OR condition: ['or' => [['field1' => 'value1'], ['field2' => 'value2']]]
     * - Nested condition: ['and' => [['field1' => 'value1'], ['or' => [['field2' => 'value2'], ['field3' => 'value3']]]]]
     *
     * @throws FilterException When the filter format is invalid
     */
    public function process(Builder $query, ?array $filters): Builder
    {
        if (empty($filters)) {
            return $query;
        }

        // Check for invalid logical operators first
        $validOperators = ['and', 'or'];
        foreach (array_keys($filters) as $key) {
            $keyLower = strtolower($key);
            if (! in_array($keyLower, $validOperators) && is_array($filters[$key])) {
                throw new FilterException("Invalid logical operator: '{$key}'. Only 'and' and 'or' are supported.");
            }
        }

        // Check for logical operators
        $hasLogicalOperator = false;
        foreach (array_keys($filters) as $key) {
            if (strtolower($key) === 'and' || strtolower($key) === 'or') {
                $hasLogicalOperator = true;
                // This is a logical operation
                $this->validateLogicalOperation($filters);

                return $this->processLogicalOperation($query, $filters);
            }
        }

        // If we get here, it's not a logical operation
        // Process as a standard filter - get the first filter name and value
        foreach ($filters as $name => $value) {
            if (isset($this->filters[$name])) {
                $filter = $this->filters[$name];

                return $filter->apply($query, $value);
            }
        }

        // If no valid filter is found, just return the query unchanged
        // We don't throw an exception here to allow for graceful handling of unknown filters
        return $query;
    }

    /**
     * Validate that the logical operation is properly formatted.
     *
     * @throws FilterException When the filter format is invalid
     */
    protected function validateLogicalOperation(array $filters): void
    {
        // Check that the operation is either 'and' or 'or'
        if (! isset($filters['and']) && ! isset($filters['or'])) {
            throw new FilterException('Logical operation must be either "and" or "or"');
        }

        // Check that only one logical operator is present
        if (isset($filters['and']) && isset($filters['or'])) {
            throw new FilterException('Cannot use both "and" and "or" operations at the same level');
        }

        // Get the operator and conditions
        $operator = isset($filters['and']) ? 'and' : 'or';
        $conditions = $filters[$operator];

        // Check that conditions is an array
        if (! is_array($conditions)) {
            throw new FilterException("Conditions for '$operator' operation must be an array");
        }

        // Check that there are at least 2 conditions
        if (count($conditions) < 2) {
            throw new FilterException("At least 2 conditions are required for '$operator' operation");
        }

        // Validate each condition
        foreach ($conditions as $condition) {
            if (! is_array($condition)) {
                throw new FilterException('Each condition must be an array');
            }

            // Check for invalid operators in nested conditions
            if (count(array_intersect_key($condition, ['and' => true, 'or' => true])) > 0) {
                // This is a nested logical operation, validate it recursively
                $this->validateLogicalOperation($condition);
            }
        }
    }

    /**
     * Process a logical operation (AND/OR) with nested conditions.
     */
    protected function processLogicalOperation(Builder $query, array $filters): Builder
    {
        // Determine the logical operator
        $operator = isset($filters['and']) ? 'and' : 'or';
        $conditions = $filters[$operator];

        // We'll wrap the conditions in a closure for proper grouping
        if ($operator === 'and') {
            $query->where(function ($subQuery) use ($conditions) {
                foreach ($conditions as $condition) {
                    // Check if this is another logical operation
                    if (isset($condition['and']) || isset($condition['or'])) {
                        $this->processLogicalOperation($subQuery, $condition);
                    } else {
                        $this->processCondition($subQuery, $condition);
                    }
                }
            });
        } else {
            // For OR conditions, we need to use orWhere closures
            $query->where(function ($subQuery) use ($conditions) {
                $first = true;

                foreach ($conditions as $condition) {
                    if ($first) {
                        // Process the first condition normally
                        if (isset($condition['and']) || isset($condition['or'])) {
                            $this->processLogicalOperation($subQuery, $condition);
                        } else {
                            $this->processCondition($subQuery, $condition);
                        }
                        $first = false;
                    } else {
                        // For subsequent conditions, use orWhere
                        $subQuery->orWhere(function ($orQuery) use ($condition) {
                            if (isset($condition['and']) || isset($condition['or'])) {
                                $this->processLogicalOperation($orQuery, $condition);
                            } else {
                                $this->processCondition($orQuery, $condition);
                            }
                        });
                    }
                }
            });
        }

        return $query;
    }

    /**
     * Process a single condition (non-logical operation)
     */
    protected function processCondition(Builder $query, array $condition): void
    {
        foreach ($condition as $name => $value) {
            if (isset($this->filters[$name])) {
                $filter = $this->filters[$name];
                $query = $filter->apply($query, $value);
            }
        }
    }
}
