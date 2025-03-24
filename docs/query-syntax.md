# LaraJobs Query Syntax Documentation

This document provides detailed information about the filtering capabilities of the LaraJobs API.

## Introduction

The LaraJobs API allows for powerful and flexible filtering of job listings through a comprehensive query syntax. This enables users to find exactly the jobs they're looking for, with support for filtering on standard fields as well as dynamic attributes.

## Basic Concepts

The API supports two main approaches to filtering:

1. **Object-based filtering**: Simple key-value pairs for straightforward queries
2. **String-based filtering**: Complex expressions with logical operators for advanced queries

## Object-Based Filtering

Object-based filtering uses a simple format where each filter is a key-value pair:

```
GET /api/jobs?filter[field]=value
```

### Format

For multiple filters, simply add more key-value pairs:

```
GET /api/jobs?filter[job_type]=full-time&filter[is_remote]=true&filter[salary_min]=70000
```

This example filters for full-time, remote jobs with a minimum salary of 70,000.

### Supported Fields

| Field        | Type       | Operators                      | Description                   |
| ------------ | ---------- | ------------------------------ | ----------------------------- |
| title        | string     | =, !=, LIKE                    | Job title                     |
| description  | string     | =, !=, LIKE                    | Job description               |
| company_name | string     | =, !=, LIKE                    | Company name                  |
| salary_min   | number     | =, !=, >, <, >=, <=            | Minimum salary                |
| salary_max   | number     | =, !=, >, <, >=, <=            | Maximum salary                |
| is_remote    | boolean    | =, !=                          | Whether job is remote         |
| job_type     | enum       | =, !=, IN                      | Type of job (full-time, etc.) |
| status       | enum       | =, !=, IN                      | Job status (published, etc.)  |
| published_at | date       | =, !=, >, <, >=, <=            | Publication date              |
| languages    | collection | =, !=, HAS_ANY, IS_ANY, EXISTS | Programming languages         |
| locations    | collection | =, !=, HAS_ANY, IS_ANY, EXISTS | Job locations                 |
| categories   | collection | =, !=, HAS_ANY, IS_ANY, EXISTS | Job categories                |
| attribute:\* | varies     | varies by type                 | Dynamic EAV attributes        |

## String-Based Filtering

String-based filtering allows for more complex queries with logical operators:

```
GET /api/jobs?filter=field=value
```

### Format

For multiple conditions with logical operators:

```
GET /api/jobs?filter=(job_type=full-time AND is_remote=true) AND salary_min>=70000
```

### Logical Operators

| Operator | Description                 | Example                   |
| -------- | --------------------------- | ------------------------- |
| AND      | All conditions must be true | condition1 AND condition2 |
| OR       | Any condition can be true   | condition1 OR condition2  |

### Comparison Operators

| Operator | Description              | Example                           |
| -------- | ------------------------ | --------------------------------- |
| =        | Equal to                 | title=Developer                   |
| !=       | Not equal to             | job_type!=freelance               |
| >        | Greater than             | salary_min>50000                  |
| <        | Less than                | salary_max<100000                 |
| >=       | Greater than or equal to | published_at>=2023-01-01          |
| <=       | Less than or equal to    | published_at<=2023-12-31          |
| LIKE     | Contains substring       | description LIKE Laravel          |
| IN       | Value in set             | job_type IN (full-time,part-time) |

### Collection Operators

For fields that contain collections (arrays):

| Operator | Description                     | Example                            |
| -------- | ------------------------------- | ---------------------------------- |
| HAS_ANY  | Collection contains any value   | languages HAS_ANY (PHP,JavaScript) |
| IS_ANY   | Matches any value in set        | locations IS_ANY (New York,Remote) |
| EXISTS   | Collection exists and not empty | categories EXISTS                  |

### Grouping Conditions

You can use parentheses to group conditions and control the order of evaluation:

```
GET /api/jobs?filter=(job_type=full-time OR job_type=part-time) AND salary_min>=70000
```

This finds jobs that are either full-time or part-time, with a minimum salary of 70,000.

## EAV Attribute Filtering

To filter by dynamic attributes, use the `attribute:` prefix:

### Basic Syntax

```
GET /api/jobs?filter=attribute:attribute_name=value
```

### Examples by Attribute Type

#### Text Attributes

```
GET /api/jobs?filter=attribute:required_skills=Laravel
GET /api/jobs?filter=attribute:required_skills LIKE PHP
```

#### Number Attributes

```
GET /api/jobs?filter=attribute:years_experience>=3
GET /api/jobs?filter=attribute:years_experience<=7
```

Range filtering:

```
GET /api/jobs?filter[attribute:years_experience][min]=3&filter[attribute:years_experience][max]=7
```

#### Boolean Attributes

```
GET /api/jobs?filter=attribute:has_benefits=true
GET /api/jobs?filter=attribute:requires_travel=false
```

#### Select Attributes

```
GET /api/jobs?filter=attribute:framework=Laravel
GET /api/jobs?filter=attribute:framework IN (Laravel,Symfony,CodeIgniter)
```

#### Date Attributes

```
GET /api/jobs?filter=attribute:start_date>=2023-01-01
GET /api/jobs?filter=attribute:certification_date<=2023-12-31
```

Range filtering:

```
GET /api/jobs?filter[attribute:certification_period][from]=2023-01-01&filter[attribute:certification_period][to]=2023-12-31
```

## Complex Query Examples

### Combining Standard and EAV Filters

```
GET /api/jobs?filter=(job_type=full-time AND salary_min>=80000) AND attribute:years_experience>=3
```

### Using Multiple Attribute Filters

```
GET /api/jobs?filter=attribute:framework=Laravel AND attribute:years_experience>=3 AND attribute:has_benefits=true
```

### Full Complex Example

```
GET /api/jobs?filter=(job_type=full-time AND (languages HAS_ANY (PHP,JavaScript))) AND (locations IS_ANY (New York,Remote)) AND attribute:years_experience>=3
```

This complex query finds:

-   Full-time jobs
-   That require either PHP or JavaScript
-   Located in New York or Remote
-   Requiring at least 3 years of experience

## Best Practices

1. **Start Simple**: Begin with basic filters and add complexity as needed
2. **Use Parentheses**: Make your logical grouping explicit with parentheses
3. **Consider Performance**: Very complex queries may be slower to process
4. **Test Your Queries**: Verify that your filters return the expected results

## Error Handling

If your filter syntax is invalid, the API will return a 400 Bad Request response:

```json
{
    "error": "Invalid filter format",
    "message": "Detailed error explanation",
    "code": "INVALID_FILTER"
}
```

Common errors include:

-   Invalid field names
-   Incompatible operators for field types
-   Malformed logical expressions
-   Syntax errors in grouping

## Filter Processing

When you submit a filter, the API processes it as follows:

1. **Parsing**: The filter string or object is parsed into a structured format
2. **Validation**: Each filter condition is validated against the allowed fields and operators
3. **Query Building**: Valid filters are translated into database query conditions
4. **Execution**: The query is executed, and matching job posts are returned

## Further Resources

-   [API Documentation](/docs/api-documentation.md): Complete documentation for all API endpoints
-   [EAV Filter Optimization](/docs/eav-filter-optimization.md): Technical details on how EAV filters are optimized
