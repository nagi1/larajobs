<?php

namespace App\Services\Filters;

use App\Contracts\Filters\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractFilter implements FilterInterface
{
    /**
     * Get the name of the filter.
     */
    abstract public function getName(): string;

    /**
     * Apply the filter to the query.
     */
    abstract public function apply(Builder $query, mixed $value): Builder;
}
