<?php

namespace App\Contracts\Filters;

use Illuminate\Database\Eloquent\Builder;

interface PipelineInterface
{
    /**
     * Add a filter to the pipeline.
     */
    public function addFilter(FilterInterface $filter): self;

    /**
     * Process the filters on the query.
     */
    public function process(Builder $query, array $filters): Builder;
}
