<?php

namespace App\Filters;

use App\Contracts\Filters\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseFilter implements FilterInterface
{
    /**
     * Create a new filter instance.
     */
    public function __construct(
        protected readonly string $column,
    ) {}

    /**
     * Get the name of the filter.
     */
    public function getName(): string
    {
        return $this->column;
    }

    /**
     * Get the column to filter on.
     */
    protected function getColumn(): string
    {
        return $this->column;
    }

    abstract public function apply(Builder $query, mixed $value): Builder;
}
