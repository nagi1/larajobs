<?php

namespace App\Filters;

use App\Contracts\Filters\FilterInterface;
use App\Models\Language;
use Illuminate\Database\Eloquent\Builder;

class LanguageFilter implements FilterInterface
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
            'is_any' => $query->whereHas('languages', function (Builder $query) use ($languageIds) {
                $query->whereIn('languages.id', $languageIds);
            })->whereRaw('(SELECT COUNT(*) FROM job_post_language WHERE job_post_language.job_post_id = job_posts.id) = 1'),
            default => throw new \InvalidArgumentException("Unsupported mode: {$mode}")
        };
    }

    /**
     * Get the name of the filter.
     */
    public function getName(): string
    {
        return 'languages';
    }
}
