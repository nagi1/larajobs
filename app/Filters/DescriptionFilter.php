<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class DescriptionFilter extends BaseFilter
{
    /**
     * Apply the filter to the query.
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->where('description', 'like', "%{$value}%");
    }
}
