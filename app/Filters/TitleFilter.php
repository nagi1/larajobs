<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class TitleFilter extends BaseFilter
{
    /**
     * Apply the filter to the query.
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->where('title', 'like', "%{$value}%");
    }
}
