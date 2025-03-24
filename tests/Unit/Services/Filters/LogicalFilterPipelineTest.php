<?php

use App\Contracts\Filters\FilterInterface;
use App\Models\JobPost;
use App\Services\Filters\LogicalFilterPipeline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a real job post for our tests
    $this->jobPost = JobPost::factory()->create();
});

// Helper function to create a mock filter
function createMockFilter(string $name, ?string $column = null): FilterInterface
{
    // If no column is provided, use the filter name as the column
    $column = $column ?? $name;

    // Create a real implementation of FilterInterface rather than a mock
    return new class($name, $column) implements FilterInterface
    {
        private string $name;

        private string $column;

        public function __construct(string $name, string $column)
        {
            $this->name = $name;
            $this->column = $column;
        }

        public function apply(Builder $query, mixed $value): Builder
        {
            return $query->where($this->column, $value);
        }

        public function getName(): string
        {
            return $this->name;
        }
    };
}

test('pipeline processes and conditions', function () {
    $pipeline = new LogicalFilterPipeline;

    // Create test filters
    $filter1 = createMockFilter('filter1', 'title');
    $filter2 = createMockFilter('filter2', 'company_name');

    // Add filters to pipeline
    $pipeline->addFilter($filter1);
    $pipeline->addFilter($filter2);

    // Create a query
    $query = JobPost::query();

    // Create filters with AND logic
    $filterData = [
        'and' => [
            ['filter1' => 'Test Title'],
            ['filter2' => 'Test Company'],
        ],
    ];

    // Process the filters
    $processedQuery = $pipeline->process($query, $filterData);

    // Check the query by examining the SQL
    $sql = $processedQuery->toSql();
    expect($sql)->toContain('where');

    // We should have 2 where clauses (one for each filter)
    $sqlParts = explode('?', $sql);
    expect(count($sqlParts) - 1)->toBe(2); // Number of parameters should be 2
});

test('pipeline processes or conditions', function () {
    $pipeline = new LogicalFilterPipeline;

    // Create test filters
    $filter1 = createMockFilter('filter1', 'title');
    $filter2 = createMockFilter('filter2', 'company_name');

    // Add filters to pipeline
    $pipeline->addFilter($filter1);
    $pipeline->addFilter($filter2);

    // Create a query
    $query = JobPost::query();

    // Create filters with OR logic
    $filterData = [
        'or' => [
            ['filter1' => 'Test Title'],
            ['filter2' => 'Test Company'],
        ],
    ];

    // Process the filters
    $processedQuery = $pipeline->process($query, $filterData);

    // Check the query by examining the SQL
    $sql = $processedQuery->toSql();
    expect($sql)->toContain('or');

    // We should have 2 where clauses (one for each filter)
    $sqlParts = explode('?', $sql);
    expect(count($sqlParts) - 1)->toBe(2); // Number of parameters should be 2
});

test('pipeline processes nested conditions', function () {
    $pipeline = new LogicalFilterPipeline;

    // Create test filters
    $filter1 = createMockFilter('filter1', 'title');
    $filter2 = createMockFilter('filter2', 'company_name');
    $filter3 = createMockFilter('filter3', 'description');

    // Add filters to pipeline
    $pipeline->addFilter($filter1);
    $pipeline->addFilter($filter2);
    $pipeline->addFilter($filter3);

    // Create a query
    $query = JobPost::query();

    // Create nested filters (AND with nested OR)
    $filterData = [
        'and' => [
            ['filter1' => 'Test Title'],
            [
                'or' => [
                    ['filter2' => 'Test Company'],
                    ['filter3' => 'Test Description'],
                ],
            ],
        ],
    ];

    // Process the filters
    $processedQuery = $pipeline->process($query, $filterData);

    // Check the query by examining the SQL
    $sql = $processedQuery->toSql();
    expect($sql)->toContain('where');
    expect($sql)->toContain('or');

    // We should have 3 where clauses (one for each filter)
    $sqlParts = explode('?', $sql);
    expect(count($sqlParts) - 1)->toBe(3); // Number of parameters should be 3
});

test('pipeline handles empty conditions', function () {
    $pipeline = new LogicalFilterPipeline;

    // Create a query
    $query = JobPost::query();

    // Empty filter data
    $filterData = [];

    // Process the filters - should just return the query without changes
    $processedQuery = $pipeline->process($query, $filterData);

    // Original SQL should not have any where clauses
    $sql = $processedQuery->toSql();
    expect($sql)->not->toContain('where');
});
