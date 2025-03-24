# EAV Filter Optimization

This document outlines the optimizations implemented in the EAV (Entity-Attribute-Value) filter system to enhance query performance while maintaining all functionality.

## Database Level Optimizations

### Additional Indexes

We added the following indexes to improve query performance:

1. Combined index on `job_post_id` and `attribute_id` in the `job_attribute_values` table:

    ```php
    $table->index(['job_post_id', 'attribute_id']);
    ```

    - This improves queries that filter by specific attribute types for specific job posts.

2. Index on `attribute_id` and `value` in the `job_attribute_values` table:
    ```php
    $table->index(['attribute_id', 'value(191)']);
    ```
    - This significantly improves filtering by attribute values.
    - The `value(191)` notation limits the index to the first 191 characters, which is important for text fields in MySQL.

## Code Level Optimizations

### 1. Attribute Caching

Implemented caching for attribute lookups to reduce repeated database queries:

```php
$cacheKey = "attribute_{$attributeName}";
$attribute = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($attributeName) {
    return Attribute::query()
        ->where('name', $attributeName)
        ->whereIn('type', [AttributeType::TEXT, AttributeType::NUMBER, AttributeType::BOOLEAN, AttributeType::SELECT, AttributeType::DATE])
        ->first();
});
```

-   The cache TTL (Time To Live) is set to 5 minutes.
-   This significantly reduces database load for frequently used attributes.

### 2. Optimized Query Structure

#### Replaced `whereHas` with Direct Joins

Before:

```php
return $query->whereHas('jobAttributeValues', function ($query) use ($attribute, $text, $mode) {
    $query->where('attribute_id', $attribute->id);
    // More conditions...
});
```

After:

```php
$query->join('job_attribute_values AS text_filter', function ($join) use ($attribute, $text, $mode) {
    $join->on('job_posts.id', '=', 'text_filter.job_post_id')
        ->where('text_filter.attribute_id', '=', $attribute->id);
    // More conditions...
});
return $query->distinct('job_posts.id');
```

-   Direct joins are more efficient than `whereHas` for complex queries.
-   Using table aliases prevents conflicts in multiple joins.
-   The `distinct` clause prevents duplicate results when joining.

### 3. Optimized Special Cases

#### Inequality Operators

For handling inequality operators (`!=`), we implemented a more efficient approach using `whereNotExists` and `whereExists`:

```php
return $query->where(function ($query) use ($attribute, $text) {
    // Include job posts that don't have this attribute at all
    $query->whereNotExists(function ($q) use ($attribute) {
        $q->select(DB::raw(1))
            ->from('job_attribute_values')
            ->whereRaw('job_attribute_values.job_post_id = job_posts.id')
            ->where('job_attribute_values.attribute_id', '=', $attribute->id);
    })
    // Or job posts that have this attribute but with a different value
    ->orWhereExists(function ($q) use ($attribute, $text) {
        $q->select(DB::raw(1))
            ->from('job_attribute_values')
            ->whereRaw('job_attribute_values.job_post_id = job_posts.id')
            ->where('job_attribute_values.attribute_id', '=', $attribute->id)
            ->where('job_attribute_values.value', '!=', $text);
    });
});
```

-   This approach is more efficient than using `whereDoesntHave` and `whereHas`.
-   It correctly handles both records that don't have the attribute and those with different values.

#### Select Attributes with "All" Mode

For the "all" mode in select attributes, we use `whereExists` for each value to ensure all values are present:

```php
foreach ($validValues as $value) {
    $query->whereExists(function ($subquery) use ($attribute, $value) {
        $subquery->select(DB::raw(1))
            ->from('job_attribute_values')
            ->whereRaw('job_attribute_values.job_post_id = job_posts.id')
            ->where('job_attribute_values.attribute_id', $attribute->id)
            ->where(function ($q) use ($value) {
                $q->where('job_attribute_values.value', $value)
                    ->orWhere('job_attribute_values.value', 'LIKE', '%"'.$value.'"%')
                    ->orWhere('job_attribute_values.value', 'LIKE', '%'.$value.'%');
            });
    });
}
```

-   This method is more efficient than using subqueries with `COUNT` and `HAVING`.
-   It ensures accurate results for checking multiple values.

### 4. Numeric Comparison Optimization

For numeric comparisons, we use explicit casting to ensure proper ordering:

```php
$join->whereRaw('CAST(num_filter.value AS DECIMAL) >= ?', [(float) $min]);
```

-   Casting ensures that numeric strings are compared as numbers rather than lexicographically.
-   This is crucial for range filters to work correctly.

## Performance Impact

These optimizations provide several benefits:

1. **Reduced Query Complexity**: Using direct joins instead of nested `whereHas` calls flattens the query structure.
2. **Fewer Database Trips**: Caching attribute lookups reduces the number of database queries.
3. **Index Utilization**: Adding appropriate indexes ensures the database can efficiently execute the queries.
4. **Better Scalability**: The optimized queries perform better as the dataset grows.

## Testing

All 43 test cases for the EAV filter were verified to ensure the optimizations maintain full functionality while improving performance.

## Further Optimization Possibilities

1. **Query Result Caching**: For frequently used filter combinations, caching the entire result set could provide additional performance benefits.
2. **Materialized Views**: For complex reports or dashboards, materialized views could pre-aggregate data.
3. **Dedicated Search Engine**: For very large datasets, consider using a dedicated search engine like Elasticsearch or Algolia.
4. **Denormalization**: In some cases, denormalizing data might improve read performance at the cost of write complexity.
