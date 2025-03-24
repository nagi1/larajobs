<?php

namespace App\Services\Filters;

use App\Enums\JobType;
use Illuminate\Database\Eloquent\Builder;

class JobTypeFilter extends AbstractFilter
{
    /**
     * Get the name of the filter.
     */
    public function getName(): string
    {
        return 'job_type';
    }

    /**
     * Apply the filter to the query.
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        if (is_array($value)) {
            return $query->whereIn('job_type', $value);
        }

        // Handle string values like 'full-time' or 'part-time'
        if (is_string($value)) {
            try {
                // Try to convert to enum if a valid case
                $validTypes = array_map(fn (JobType $type) => $type->value, JobType::cases());
                if (in_array($value, $validTypes)) {
                    return $query->where('job_type', $value);
                }
            } catch (\Throwable $th) {
                // If conversion fails, just use the string value
                return $query->where('job_type', $value);
            }
        }

        return $query;
    }
}
