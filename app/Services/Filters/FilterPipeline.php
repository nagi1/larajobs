<?php

namespace App\Services\Filters;

use App\Contracts\Filters\FilterInterface;
use App\Contracts\Filters\PipelineInterface;
use Illuminate\Database\Eloquent\Builder;

class FilterPipeline implements PipelineInterface
{
    /**
     * The collection of filters.
     *
     * @var array<string, FilterInterface>
     */
    protected array $filters = [];

    /**
     * Add a filter to the pipeline.
     *
     * @return $this
     */
    public function addFilter(FilterInterface $filter): self
    {
        $this->filters[$filter->getName()] = $filter;

        return $this;
    }

    /**
     * Process filters on the query.
     */
    public function process(Builder $query, ?array $filters): Builder
    {
        if (empty($filters)) {
            return $query;
        }

        foreach ($this->filters as $name => $filter) {
            if (isset($filters[$name])) {
                $query = $filter->apply($query, $filters[$name]);
            }
        }

        return $query;
    }
}
