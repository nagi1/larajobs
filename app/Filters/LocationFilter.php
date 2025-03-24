<?php

namespace App\Filters;

use App\Contracts\Filters\FilterInterface;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;

class LocationFilter implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        if (isset($value['mode']) && $value['mode'] === 'exists') {
            return $query->has('locations');
        }

        $values = isset($value['values']) ? $value['values'] : $value;
        $field = isset($value['field']) ? strtolower($value['field']) : 'city';

        if (! in_array($field, ['city', 'state', 'country'])) {
            throw new \InvalidArgumentException("Invalid field: {$field}");
        }

        if (is_string($values)) {
            $values = [trim($values)];
        } elseif (is_array($values)) {
            $values = array_map('trim', $values);
        }

        $locationIds = Location::query()
            ->where(function ($query) use ($values, $field) {
                foreach ($values as $value) {
                    $query->orWhereRaw("LOWER({$field}) = LOWER(?)", [trim($value)]);
                }
            })
            ->pluck('id')
            ->toArray();

        if (empty($locationIds)) {
            return $query;
        }

        $mode = isset($value['mode']) ? $value['mode'] : 'has_any';

        return match ($mode) {
            'exists' => $query->has('locations'),
            '=' => $query->whereHas('locations', function (Builder $query) use ($locationIds) {
                $query->whereIn('locations.id', $locationIds);
            }, '=', count($locationIds))->whereDoesntHave('locations', function (Builder $query) use ($locationIds) {
                $query->whereNotIn('locations.id', $locationIds);
            }),
            'has_any' => $query->whereHas('locations', function (Builder $query) use ($locationIds) {
                $query->whereIn('locations.id', $locationIds);
            }),
            'is_any' => $query->whereHas('locations', function (Builder $query) use ($locationIds) {
                $query->whereIn('locations.id', $locationIds);
            })->whereRaw('(SELECT COUNT(*) FROM job_post_location WHERE job_post_location.job_post_id = job_posts.id) = 1'),
            default => throw new \InvalidArgumentException("Unsupported mode: {$mode}")
        };
    }

    /**
     * Get the name of the filter.
     */
    public function getName(): string
    {
        return 'locations';
    }
}
