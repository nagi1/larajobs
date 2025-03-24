<?php

namespace App\Services\Filters;

use Illuminate\Database\Eloquent\Builder;

class SalaryMinFilter extends AbstractFilter
{
    /**
     * Get the name of the filter.
     */
    public function getName(): string
    {
        return 'salary_min';
    }

    /**
     * Apply the filter to the query.
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        if (is_numeric($value)) {
            // Jobs with salary_min greater than or equal to the value
            return $query->where('salary_min', '>=', (float) $value);
        }

        return $query;
    }
}
