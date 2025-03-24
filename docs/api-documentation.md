# LaraJobs API Documentation

This document provides detailed information about the LaraJobs API, which offers advanced job filtering capabilities.

## Base URL

```
https://api.larajobs.com
```

## Authentication

The API currently supports public access for job listings. Authentication will be implemented in future versions.

## API Endpoints

### Get Job Listings

```
GET /api/jobs
```

Retrieves a paginated list of job postings that match the specified criteria.

#### Query Parameters

| Parameter | Type   | Description                                 | Default    |
| --------- | ------ | ------------------------------------------- | ---------- |
| filter    | mixed  | Filter criteria to narrow down job listings | null       |
| sort      | string | Field to sort results by                    | created_at |
| order     | string | Sort order, either 'asc' or 'desc'          | desc       |
| per_page  | int    | Number of results per page (max 100)        | 15         |
| page      | int    | Page number for pagination                  | 1          |

#### Response Format

The API returns JSON responses with the following structure:

```json
{
    "data": [
        {
            "id": 123,
            "title": "Senior Laravel Developer",
            "company_name": "Acme Inc",
            "description": "Seeking an experienced Laravel developer...",
            "salary_min": 80000,
            "salary_max": 120000,
            "is_remote": true,
            "job_type": "full-time",
            "status": "published",
            "published_at": "2023-06-15T12:00:00Z",
            "attributes": [
                {
                    "name": "years_experience",
                    "type": "number",
                    "value": "5"
                },
                {
                    "name": "skills",
                    "type": "select",
                    "value": ["PHP", "Laravel", "Vue.js"]
                }
            ],
            "languages": ["PHP", "JavaScript"],
            "locations": ["New York", "Remote"],
            "categories": ["Web Development", "Backend"]
        }
        // More job listings...
    ],
    "links": {
        "first": "https://api.larajobs.com/api/jobs?page=1",
        "last": "https://api.larajobs.com/api/jobs?page=10",
        "prev": null,
        "next": "https://api.larajobs.com/api/jobs?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "path": "https://api.larajobs.com/api/jobs",
        "per_page": 15,
        "to": 15,
        "total": 150,
        "filters": "...",
        "sort": "title",
        "order": "asc"
    }
}
```

## Filtering

The API supports two methods for filtering:

### 1. Object-Based Filtering

You can pass an object with key-value pairs to filter job listings. For example:

```
GET /api/jobs?filter[job_type]=full-time&filter[is_remote]=true&filter[salary_min]=70000
```

### 2. String-Based Filtering

For more complex filtering needs, you can use string notation with logical operators:

```
GET /api/jobs?filter=(job_type=full-time AND is_remote=true) AND salary_min>=70000
```

#### Supported Operators

##### Logical Operators

| Operator | Description                               |
| -------- | ----------------------------------------- |
| AND      | Logical AND between conditions            |
| OR       | Logical OR between conditions             |
| ( )      | Grouping conditions to control precedence |

##### Comparison Operators

| Operator | Description                          |
| -------- | ------------------------------------ |
| =        | Equal to                             |
| !=       | Not equal to                         |
| >        | Greater than                         |
| <        | Less than                            |
| >=       | Greater than or equal to             |
| <=       | Less than or equal to                |
| LIKE     | Contains substring (for text fields) |
| IN       | Value matches any in a set           |

##### Collection Operators

| Operator | Description                                |
| -------- | ------------------------------------------ |
| HAS_ANY  | Collection has any of the specified values |
| IS_ANY   | Collection is any of the specified values  |
| EXISTS   | Collection exists                          |

### EAV Attribute Filtering

To filter by dynamic attributes, use the `attribute:` prefix:

```
GET /api/jobs?filter=attribute:years_experience>=3
```

#### Text Attributes

```
GET /api/jobs?filter=attribute:description=Laravel
GET /api/jobs?filter=attribute:description LIKE Vue
```

#### Number Attributes

Simple comparison:

```
GET /api/jobs?filter=attribute:years_experience>=3
```

Range filtering:

```
GET /api/jobs?filter[attribute:years_experience][min]=3&filter[attribute:years_experience][max]=7
```

#### Boolean Attributes

```
GET /api/jobs?filter=attribute:has_benefits=true
```

#### Select Attributes

```
GET /api/jobs?filter=attribute:skills=PHP
GET /api/jobs?filter=attribute:skills IN (PHP,Laravel,Vue.js)
```

#### Date Attributes

Simple comparison:

```
GET /api/jobs?filter=attribute:certification_date>=2023-01-01
```

Range filtering:

```
GET /api/jobs?filter[attribute:certification_date][from]=2023-01-01&filter[attribute:certification_date][to]=2023-12-31
```

#### Complex Example

```
GET /api/jobs?filter=(job_type=full-time AND (languages HAS_ANY (PHP,JavaScript))) AND (locations IS_ANY (New York,Remote)) AND attribute:years_experience>=3
```

## Error Handling

The API uses standard HTTP status codes to indicate the success or failure of a request.

### Error Response Format

```json
{
    "error": "Error title",
    "message": "Detailed error message",
    "code": "ERROR_CODE"
}
```

### Common Error Codes

| Code             | Description                               |
| ---------------- | ----------------------------------------- |
| INVALID_FILTER   | The filter syntax is invalid or malformed |
| VALIDATION_ERROR | One or more validation rules failed       |

#### Example Error Response

```json
{
    "error": "Invalid filter format",
    "message": "The filter contains an invalid logical operator",
    "code": "INVALID_FILTER"
}
```

## Pagination

The API returns paginated results with links to navigate through the result set.

### Pagination Parameters

| Parameter | Type | Description         | Default |
| --------- | ---- | ------------------- | ------- |
| per_page  | int  | Items per page      | 15      |
| page      | int  | Current page number | 1       |

### Pagination Response

The `meta` object in the response contains pagination details:

```json
"meta": {
  "current_page": 1,
  "from": 1,
  "last_page": 10,
  "path": "https://api.larajobs.com/api/jobs",
  "per_page": 15,
  "to": 15,
  "total": 150
}
```

## Rate Limiting

The API implements rate limiting to ensure fair usage:

-   Unauthenticated requests: 60 requests per minute
-   Authenticated requests: 1000 requests per minute

Rate limit information is included in the response headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1623423543
```

## Data Models

### Job Post

| Field        | Type      | Description                              |
| ------------ | --------- | ---------------------------------------- |
| id           | integer   | Unique identifier                        |
| title        | string    | Job title                                |
| description  | text      | Job description                          |
| company_name | string    | Name of the company                      |
| salary_min   | decimal   | Minimum salary                           |
| salary_max   | decimal   | Maximum salary                           |
| is_remote    | boolean   | Whether the job is remote                |
| job_type     | enum      | Type of job (full-time, part-time, etc.) |
| status       | enum      | Job status (draft, published, archived)  |
| published_at | timestamp | When the job was published               |
| created_at   | timestamp | When the job was created                 |
| updated_at   | timestamp | When the job was last updated            |
| languages    | array     | Programming languages required           |
| locations    | array     | Possible job locations                   |
| categories   | array     | Job categories                           |
| attributes   | array     | Custom attributes (EAV)                  |

## Examples

### Basic Job Search

```
GET /api/jobs?filter[job_type]=full-time&filter[is_remote]=true&sort=published_at&order=desc
```

### Complex Filter Request

```
GET /api/jobs?filter=(job_type=full-time AND (languages HAS_ANY (PHP,JavaScript))) AND (locations IS_ANY (New York,Remote)) AND attribute:years_experience>=3
```

This query finds:

-   Full-time jobs
-   Requiring either PHP or JavaScript
-   Located in New York or Remote
-   Requiring at least 3 years of experience
