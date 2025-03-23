<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class IsRemoteFilter extends BaseFilter
{
    public function __construct()
    {
        parent::__construct('is_remote');
    }

    /**
     * Apply the filter to the query.
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        if (! is_bool($value) && ! in_array($value, ['true', 'false', '0', '1'], true)) {
            return $query;
        }

        $isRemote = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        return $query->where('is_remote', $isRemote);
    }
}
