<?php

namespace App\Filters;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Builder;

class StatusFilter extends BaseFilter
{
    public function __construct()
    {
        parent::__construct('status');
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        $validStatuses = $this->getValidStatuses($value);

        if (empty($validStatuses)) {
            return $query;
        }

        return $query->whereIn($this->column, $validStatuses);
    }

    private function getValidStatuses(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];

        return array_filter($values, fn ($status) => JobStatus::tryFrom($status) !== null);
    }
}
