<?php

namespace App\Filters;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

class CategoryFilter
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        if (isset($value['mode']) && $value['mode'] === 'exists') {
            return $query->has('categories');
        }

        $values = isset($value['values']) ? $value['values'] : $value;

        if (is_string($values)) {
            $values = [trim($values)];
        } elseif (is_array($values)) {
            $values = array_map('trim', $values);
        }

        $categoryIds = Category::query()
            ->where(function ($query) use ($values) {
                foreach ($values as $value) {
                    $query->orWhereRaw('LOWER(name) = LOWER(?)', [trim($value)]);
                }
            })
            ->pluck('id')
            ->toArray();

        if (empty($categoryIds)) {
            return $query;
        }

        $mode = isset($value['mode']) ? $value['mode'] : 'has_any';

        return match ($mode) {
            'exists' => $query->has('categories'),
            '=' => $query->whereHas('categories', function (Builder $query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            }, '=', count($categoryIds))->whereDoesntHave('categories', function (Builder $query) use ($categoryIds) {
                $query->whereNotIn('categories.id', $categoryIds);
            }),
            'has_any' => $query->whereHas('categories', function (Builder $query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            }),
            'is_any' => $query->has('categories', '=', 1)->whereHas('categories', function (Builder $query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            }),
            default => throw new \InvalidArgumentException("Unsupported mode: {$mode}")
        };
    }
}
