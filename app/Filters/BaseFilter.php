<?php

namespace App\Filters;

use App\Contracts\Filters\FilterInterface;

abstract class BaseFilter implements FilterInterface
{
    /**
     * Create a new filter instance.
     */
    public function __construct(
        protected string $name,
        protected string $column,
    ) {}

    /**
     * Get the name of the filter.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the column to filter on.
     */
    protected function getColumn(): string
    {
        return $this->column;
    }
}
