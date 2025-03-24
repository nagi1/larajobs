# LaraJobs API Documentation

This document provides detailed information about the LaraJobs API, which offers advanced job filtering capabilities.

## Base URL

```
https://astudio-larajobs.test
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
| filter    | string | Filter criteria to narrow down job listings | null       |
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
            "published_at": "2023-06-15T10:30:00Z",
            "created_at": "2023-06-10T15:45:22Z",
            "updated_at": "2023-06-15T10:30:00Z",
            "languages": ["PHP", "JavaScript"],
            "locations": ["New York", "Remote"],
            "categories": ["Backend", "Web Development"],
            "attributes": {
                "years_experience": "5+",
                "education_level": "Bachelor's",
                "benefits": true
            }
        }
        // Additional job posts...
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "path": "https://astudio-larajobs.test/api/jobs",
        "per_page": 15,
        "to": 15,
        "total": 150
    },
    "links": {
        "first": "https://astudio-larajobs.test/api/jobs?page=1",
        "last": "https://astudio-larajobs.test/api/jobs?page=10",
        "prev": null,
        "next": "https://astudio-larajobs.test/api/jobs?page=2"
    }
}
```

## Filtering

The API provides powerful filtering capabilities using a string-based query format. You can filter jobs based on standard fields, relationships, and dynamic attributes.

### Filter Syntax

The basic filter syntax is:

```
field_name operator value
```

For example:

```
job_type=full-time
```

Multiple conditions can be combined using logical operators:

```
(job_type=full-time OR job_type=contract) AND is_remote=true
```

### Standard Field Filtering

Filter by job title, company name, salary, status, etc.

#### Text Fields

```
GET /api/jobs?filter=title LIKE "Laravel Developer"
GET /api/jobs?filter=company_name="Acme Inc"
```

#### Numeric Fields

```
GET /api/jobs?filter=salary_min>=50000
GET /api/jobs?filter=salary_max<=100000
```

#### Boolean Fields

```
GET /api/jobs?filter=is_remote=true
```

#### Enum Fields

```
GET /api/jobs?filter=job_type=full-time
GET /api/jobs?filter=status IN (published,featured)
```

#### Date Fields

```
GET /api/jobs?filter=published_at>=2023-01-01
GET /api/jobs?filter=published_at<=2023-12-31
```

### Relationship Filtering

Filter by related entities like languages, locations, or categories.

#### Languages

```
GET /api/jobs?filter=languages HAS_ANY (PHP,JavaScript)
```

#### Locations

```
GET /api/jobs?filter=locations IS_ANY (New York,Remote)
```

#### Categories

```
GET /api/jobs?filter=categories EXISTS
GET /api/jobs?filter=categories HAS_ANY (Backend,Frontend)
```

### Attribute (EAV) Filtering

Filter by dynamic attributes using the `attribute:` prefix.

#### Text Attributes

```
GET /api/jobs?filter=attribute:description=Laravel
GET /api/jobs?filter=attribute:description LIKE Vue
```

#### Number Attributes

```
GET /api/jobs?filter=attribute:years_experience>=3
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

```
GET /api/jobs?filter=attribute:certification_date>=2023-01-01
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
  "path": "https://astudio-larajobs.test/api/jobs",
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
GET /api/jobs?filter=job_type=full-time AND is_remote=true&sort=published_at&order=desc
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
