<?php

namespace App\Filters;

use App\Enums\AttributeType;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Builder;

class EavFilter
{
    /**
     * Apply the EAV filter to the query
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value) || ! is_array($value)) {
            return $query;
        }

        $attributeName = $value['name'] ?? null;
        $operator = $value['operator'] ?? '=';
        $attributeValue = $value['value'] ?? null;

        $attribute = Attribute::query()
            ->where('name', $attributeName)
            ->whereIn('type', [AttributeType::TEXT, AttributeType::NUMBER, AttributeType::BOOLEAN, AttributeType::SELECT])
            ->first();

        if (! $attribute) {
            return $query;
        }

        return match ($attribute->type) {
            AttributeType::TEXT => $this->handleTextFilter($query, $attribute, $value),
            AttributeType::NUMBER => $this->handleNumberFilter($query, $attribute, $value),
            AttributeType::BOOLEAN => $this->handleBooleanFilter($query, $attribute, $value),
            AttributeType::SELECT => $this->handleSelectFilter($query, $attribute, $value),
            default => $query,
        };
    }

    private function handleTextFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;
        if (! $value) {
            return $query;
        }

        $operator = $filter['operator'] ?? '=';

        return $query->whereHas('jobAttributeValues', function (Builder $query) use ($attribute, $operator, $value) {
            $query->where('attribute_id', $attribute->id)
                ->when($operator === 'like', function (Builder $query) use ($value) {
                    $query->where('value', 'like', "%{$value}%");
                })
                ->when($operator !== 'like', function (Builder $query) use ($operator, $value) {
                    $query->where('value', $operator, $value);
                });
        });
    }

    private function handleNumberFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;
        if (! $value) {
            return $query;
        }

        $operator = $filter['operator'] ?? '=';

        return $query->whereHas('jobAttributeValues', function (Builder $query) use ($attribute, $operator, $value) {
            $query->where('attribute_id', $attribute->id)
                ->where('value', $operator, $value);
        });
    }

    private function handleBooleanFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;

        if (is_null($value)) {
            return $query;
        }

        $boolValue = is_bool($value) ? $value : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $query->whereHas('jobAttributeValues', function (Builder $query) use ($attribute, $boolValue) {
            $query->where('attribute_id', $attribute->id)
                ->where('value', $boolValue === true ? '1' : '0');
        });
    }

    private function handleSelectFilter(Builder $query, Attribute $attribute, array $filter): Builder
    {
        $value = $filter['value'] ?? null;
        if (! $value) {
            return $query;
        }

        $operator = $filter['operator'] ?? '=';
        $values = is_array($value) ? $value : [$value];

        // Convert values to lowercase for case-insensitive comparison
        $values = array_map('strtolower', $values);
        $validOptions = array_map('strtolower', $attribute->options);

        // Filter out invalid options
        $values = array_values(array_filter($values, fn ($v) => in_array($v, $validOptions)));

        if (empty($values)) {
            return $query->whereRaw('1 = 0'); // Force empty result set for invalid values
        }

        return $query->whereHas('jobAttributeValues', function ($query) use ($attribute, $values, $operator) {
            $query->where('attribute_id', $attribute->id);

            if ($operator === 'in' && count($values) > 1) {
                $query->where(function ($q) use ($values) {
                    foreach ($values as $value) {
                        $q->orWhereRaw('LOWER(value) = ?', [strtolower($value)]);
                    }
                });
            } else {
                $query->whereRaw('LOWER(value) = ?', [strtolower($values[0])]);
            }
        });
    }
}
