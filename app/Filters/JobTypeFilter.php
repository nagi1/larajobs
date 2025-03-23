<?php

namespace App\Filters;

use App\Enums\JobType;
use Illuminate\Database\Eloquent\Builder;

class JobTypeFilter extends BaseFilter
{
    public function __construct()
    {
        parent::__construct('job_type');
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        $validTypes = $this->getValidJobTypes($value);

        if (empty($validTypes)) {
            return $query;
        }

        return $query->whereIn($this->column, $validTypes);
    }

    private function getValidJobTypes(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];

        return array_filter($values, fn ($type) => JobType::tryFrom($type) !== null);
    }
}
