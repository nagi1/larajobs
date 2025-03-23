<?php

namespace App\Filters;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;

class LocationFilter
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
            'is_any' => $query->has('locations', '=', 1)->whereHas('locations', function (Builder $query) use ($locationIds) {
                $query->whereIn('locations.id', $locationIds);
            }),
            default => throw new \InvalidArgumentException("Unsupported mode: {$mode}")
        };
    }
}
