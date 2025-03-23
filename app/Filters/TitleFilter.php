<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class TitleFilter extends BaseFilter
{
    public function __construct()
    {
        parent::__construct('title');
    }

    /**
     * Apply the filter to the query.
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->where('title', 'like', "%{$value}%");
    }
}
