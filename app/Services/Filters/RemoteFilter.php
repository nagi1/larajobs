<?php

namespace App\Services\Filters;

use Illuminate\Database\Eloquent\Builder;

class RemoteFilter extends AbstractFilter
{
    /**
     * Get the name of the filter.
     */
    public function getName(): string
    {
        return 'is_remote';
    }

    /**
     * Apply the filter to the query.
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        // Convert various representations of boolean values
        if (is_string($value)) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        // Only apply if we have a valid boolean
        if (is_bool($value)) {
            return $query->where('is_remote', $value);
        }

        return $query;
    }
}
