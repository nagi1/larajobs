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

        if (! $attributeName || ! $attributeValue) {
            return $query;
        }

        $attribute = Attribute::query()
            ->where('name', $attributeName)
            ->whereIn('type', [AttributeType::TEXT, AttributeType::NUMBER])
            ->first();

        if (! $attribute) {
            return $query;
        }

        return $query->whereHas('jobAttributeValues', function (Builder $query) use ($attribute, $operator, $attributeValue) {
            $query->where('attribute_id', $attribute->id)
                ->when($attribute->type === AttributeType::TEXT, function (Builder $query) use ($operator, $attributeValue) {
                    $query->where('value', $operator === 'like' ? 'like' : $operator, $operator === 'like' ? "%{$attributeValue}%" : $attributeValue);
                })
                ->when($attribute->type === AttributeType::NUMBER, function (Builder $query) use ($operator, $attributeValue) {
                    $query->where('value', $operator, $attributeValue);
                });
        });
    }
}
