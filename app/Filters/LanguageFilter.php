<?php

namespace App\Filters;

use App\Models\Language;
use Illuminate\Database\Eloquent\Builder;

class LanguageFilter
{
    /**
     * Apply the language filter to the query
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        if (isset($value['mode']) && $value['mode'] === 'exists') {
            return $query->has('languages');
        }

        $values = isset($value['values']) ? $value['values'] : $value;

        if (is_string($values)) {
            $values = [trim($values)];
        } elseif (is_array($values)) {
            $values = array_map('trim', $values);
        }

        $languageIds = Language::query()
            ->where(function ($query) use ($values) {
                foreach ($values as $value) {
                    $query->orWhereRaw('LOWER(name) = LOWER(?)', [trim($value)]);
                }
            })
            ->pluck('id')
            ->toArray();

        if (empty($languageIds)) {
            return $query;
        }

        $mode = isset($value['mode']) ? $value['mode'] : 'has_any';

        return match ($mode) {
            'exists' => $query->has('languages'),
            '=' => $query->whereHas('languages', function (Builder $query) use ($languageIds) {
                $query->whereIn('languages.id', $languageIds);
            }, '=', count($languageIds))->whereDoesntHave('languages', function (Builder $query) use ($languageIds) {
                $query->whereNotIn('languages.id', $languageIds);
            }),
            'has_any' => $query->whereHas('languages', function (Builder $query) use ($languageIds) {
                $query->whereIn('languages.id', $languageIds);
            }),
            'is_any' => $query->has('languages', '=', 1)->whereHas('languages', function (Builder $query) use ($languageIds) {
                $query->whereIn('languages.id', $languageIds);
            }),
            default => throw new \InvalidArgumentException("Unsupported mode: {$mode}")
        };
    }
}
