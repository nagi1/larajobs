<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class SalaryRangeFilter extends BaseFilter
{
    public function __construct()
    {
        parent::__construct('salary');
    }

    /**
     * Apply the filter to the query.
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        if (! is_array($value)) {
            return $query;
        }

        if (isset($value['min'])) {
            $query->where('salary_min', '>=', $value['min']);
        }

        if (isset($value['max'])) {
            $query->where('salary_max', '<=', $value['max']);
        }

        return $query;
    }
}
