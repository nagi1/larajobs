# Laravel Job Board API with Advanced Filtering

A powerful Laravel application that manages job listings with complex filtering capabilities similar to Airtable. The system utilizes both traditional relational database models and Entity-Attribute-Value (EAV) design patterns to handle jobs with varying attributes.

## Features

-   RESTful API for job listings
-   Advanced filtering capabilities with support for:
    -   Standard field filtering (text, numeric, boolean, enum, date)
    -   Relationship filtering (languages, locations, categories)
    -   Dynamic attribute filtering using EAV pattern
    -   Complex logical operations (AND/OR with grouping)
-   Supports various comparison operators (=, !=, >, <, >=, <=, LIKE, IN)
-   Relationship operations (HAS_ANY, IS_ANY, EXISTS)

## Documentation

Comprehensive documentation is available in the `docs` directory:

-   [API Documentation](docs/api-documentation.md) - Detailed information about API endpoints and usage
-   [Query Syntax](docs/query-syntax.md) - In-depth explanation of the filter query syntax and capabilities

A Postman collection is also included for testing the API:

-   [Postman Collection](docs/Laravel_Job_Board_API.postman_collection.json)

## Setup Instructions

### Prerequisites

-   PHP 8.3 or higher
-   Composer
-   MySQL
-   Laravel 12

### Installation

1. Clone the repository

```bash
git clone https://github.com/nagi1/laravel-job-board.git
cd laravel-job-board
```

2. Install dependencies

```bash
composer install
```

3. Set up environment variables

```bash
cp .env.example .env
```

4. Configure your database in the `.env` file

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_job_board
DB_USERNAME=root
DB_PASSWORD=
```

5. Generate application key

```bash
php artisan key:generate
```

6. Run migrations and seeders

```bash
php artisan migrate --seed
```

7. Start the development server

```bash
php artisan serve
```

## API Documentation

The main endpoint for accessing and filtering jobs is:

```
GET /api/jobs
```

### Basic Filtering

Filter jobs using simple field conditions:

```
/api/jobs?filter=title LIKE "Laravel Developer"
/api/jobs?filter=is_remote=true
/api/jobs?filter=job_type=full-time
```

### Numeric Range Filtering

Filter by numeric ranges:

```
/api/jobs?filter=salary_min>=50000
/api/jobs?filter=salary_max<=100000
```

### Date Filtering

Filter by dates:

```
/api/jobs?filter=published_at>=2023-01-01
/api/jobs?filter=created_at<=2023-12-31
```

### Relationship Filtering

Filter by related entities:

```
/api/jobs?filter=languages HAS_ANY (PHP,JavaScript)  // Jobs requiring PHP OR JavaScript
/api/jobs?filter=locations IS_ANY (New York,Remote)  // Jobs located ONLY in New York OR Remote
/api/jobs?filter=categories EXISTS                   // Jobs with any categories
```

### Attribute (EAV) Filtering

Filter by dynamic attributes:

```
/api/jobs?filter=attribute:years_experience>=3       // Jobs requiring 3+ years of experience
/api/jobs?filter=attribute:education_level=Bachelor  // Jobs requiring Bachelor's degree
```

### Logical Operators

Combine multiple filters with logical operators:

```
/api/jobs?filter=(job_type=full-time OR job_type=contract) AND is_remote=true
```

### Complex Example

Here's an example of a complex filter combining multiple conditions:

```
/api/jobs?filter=(job_type=full-time AND (languages HAS_ANY (PHP,JavaScript))) AND (locations IS_ANY (New York,Remote)) AND attribute:years_experience>=3
```

This will return full-time jobs that require either PHP or JavaScript, are located only in New York or Remote, and require at least 3 years of experience.

### Pagination and Sorting

The API supports pagination and sorting:

```
/api/jobs?filter=job_type=full-time&sort=published_at&order=desc&per_page=15
```

## Filter Syntax Reference

### Operators for Text Fields

-   `=` Equal to
-   `!=` Not equal to
-   `LIKE` Contains (uses SQL LIKE with % wildcards)

### Operators for Numeric Fields

-   `=` Equal to
-   `!=` Not equal to
-   `>` Greater than
-   `<` Less than
-   `>=` Greater than or equal to
-   `<=` Less than or equal to

### Operators for Boolean Fields

-   `=` Equal to
-   `!=` Not equal to

### Operators for Enum Fields

-   `=` Equal to
-   `!=` Not equal to
-   `IN` In a list of values

### Operators for Date Fields

-   `=` Equal to
-   `!=` Not equal to
-   `>` After
-   `<` Before
-   `>=` On or after
-   `<=` On or before

### Operators for Relationships

-   `=` Exact match (all values must match)
-   `HAS_ANY` Has any of the specified values
-   `IS_ANY` Is exactly one of the specified values
-   `EXISTS` Relationship exists

### Logical Operators

-   `AND` Logical AND
-   `OR` Logical OR
-   `()` Grouping

## Database Schema

The application uses the following database structure:

### Core Tables

-   `job_posts` - Stores basic job information
-   `languages` - Available programming languages
-   `locations` - Job locations
-   `categories` - Job categories

### Pivot Tables

-   `job_post_language` - Links jobs to languages
-   `job_post_location` - Links jobs to locations
-   `category_job_post` - Links jobs to categories

### EAV Tables

-   `attributes` - Defines dynamic attributes (name, type, options)
-   `job_attribute_values` - Stores attribute values for jobs

## Design Decisions and Trade-offs

### Entity-Attribute-Value (EAV) Implementation

-   **Pro**: Allows for flexible, dynamic attributes that vary by job type
-   **Con**: More complex queries compared to a traditional schema
-   **Decision**: Used EAV for truly variable attributes while keeping standard fields in the main table

### Query Efficiency

-   Used eager loading to prevent N+1 query problems
-   Implemented appropriate indexes on commonly filtered fields
-   Used query builder for constructing efficient SQL queries

## Testing

A Postman collection is included for testing the API. You can import it from:

`docs/Laravel_Job_Board_API.postman_collection.json`

For more detailed information on testing and optimization, refer to the documentation:

-   [API Documentation](docs/api-documentation.md)
-   [Query Syntax](docs/query-syntax.md)

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
