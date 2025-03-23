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
            ->whereIn('type', [AttributeType::TEXT, AttributeType::NUMBER, AttributeType::BOOLEAN])
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
                })
                ->when($attribute->type === AttributeType::BOOLEAN, function (Builder $query) use ($attributeValue) {
                    $boolValue = is_bool($attributeValue) ? $attributeValue : filter_var($attributeValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                    $query->where('value', $boolValue === true ? '1' : '0');
                });
        });
    }
}
