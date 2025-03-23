<?php

namespace App\Contracts\Filters;

use Illuminate\Database\Eloquent\Builder;

interface FilterInterface
{
    /**
     * Apply the filter to the query.
     */
    public function apply(Builder $query, mixed $value): Builder;

    /**
     * Get the name of the filter.
     */
    public function getName(): string;
}
