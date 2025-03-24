<?php

namespace Tests\Unit\Services\Filters;

use App\Contracts\Filters\FilterInterface;
use App\Models\JobPost;
use App\Services\Filters\FilterPipeline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_can_add_filters()
    {
        $pipeline = new FilterPipeline;

        $filter1 = $this->createMockFilter('test_filter_1');
        $filter2 = $this->createMockFilter('test_filter_2');

        $pipeline->addFilter($filter1);
        $pipeline->addFilter($filter2);

        // Reflection to check if the filters were added
        $reflection = new \ReflectionClass($pipeline);
        $property = $reflection->getProperty('filters');
        $property->setAccessible(true);

        $filters = $property->getValue($pipeline);

        $this->assertCount(2, $filters);
        $this->assertSame($filter1, $filters['test_filter_1']);
        $this->assertSame($filter2, $filters['test_filter_2']);
    }

    public function test_pipeline_can_process_filters()
    {
        $pipeline = new FilterPipeline;

        // Create two mock filters
        $filter1 = $this->createMockFilter('test_filter_1');
        $filter2 = $this->createMockFilter('test_filter_2');

        // Set expectations for the mock filters
        $filter1->expects($this->once())
            ->method('apply')
            ->willReturnCallback(function (Builder $query, $value) {
                return $query->where('test_column_1', $value);
            });

        $filter2->expects($this->once())
            ->method('apply')
            ->willReturnCallback(function (Builder $query, $value) {
                return $query->where('test_column_2', $value);
            });

        $pipeline->addFilter($filter1);
        $pipeline->addFilter($filter2);

        // Create a simple query
        $query = JobPost::query();

        // Process filters
        $filterData = [
            'test_filter_1' => 'value1',
            'test_filter_2' => 'value2',
        ];

        $processedQuery = $pipeline->process($query, $filterData);

        // Get the query builder's 'wheres' property to check if our filters were applied
        $queryWheres = $processedQuery->getQuery()->wheres;

        $this->assertCount(2, $queryWheres);
        $this->assertEquals('Basic', $queryWheres[0]['type']);
        $this->assertEquals('test_column_1', $queryWheres[0]['column']);
        $this->assertEquals('value1', $queryWheres[0]['value']);

        $this->assertEquals('Basic', $queryWheres[1]['type']);
        $this->assertEquals('test_column_2', $queryWheres[1]['column']);
        $this->assertEquals('value2', $queryWheres[1]['value']);
    }

    public function test_pipeline_skips_filters_not_in_data()
    {
        $pipeline = new FilterPipeline;

        // Create two mock filters but only provide data for one
        $filter1 = $this->createMockFilter('test_filter_1');
        $filter2 = $this->createMockFilter('test_filter_2');

        // Set expectations for the mock filters
        $filter1->expects($this->once())
            ->method('apply')
            ->willReturnCallback(function (Builder $query, $value) {
                return $query->where('test_column_1', $value);
            });

        $filter2->expects($this->never())
            ->method('apply');

        $pipeline->addFilter($filter1);
        $pipeline->addFilter($filter2);

        // Create a simple query
        $query = JobPost::query();

        // Process filters with only one filter in the data
        $filterData = [
            'test_filter_1' => 'value1',
            // test_filter_2 is missing
        ];

        $processedQuery = $pipeline->process($query, $filterData);

        // Get the query builder's 'wheres' property to check if our filter was applied
        $queryWheres = $processedQuery->getQuery()->wheres;

        $this->assertCount(1, $queryWheres);
        $this->assertEquals('Basic', $queryWheres[0]['type']);
        $this->assertEquals('test_column_1', $queryWheres[0]['column']);
        $this->assertEquals('value1', $queryWheres[0]['value']);
    }

    public function test_pipeline_handles_null_or_empty_filter_data()
    {
        $pipeline = new FilterPipeline;

        $filter = $this->createMockFilter('test_filter');
        $filter->expects($this->never())->method('apply');

        $pipeline->addFilter($filter);

        // Create a simple query
        $query = JobPost::query();

        // Test with null filter data
        $processedQuery1 = $pipeline->process($query, null);
        $this->assertEmpty($processedQuery1->getQuery()->wheres);

        // Test with empty array filter data
        $processedQuery2 = $pipeline->process($query, []);
        $this->assertEmpty($processedQuery2->getQuery()->wheres);
    }

    public function test_filters_are_chained_in_sequence()
    {
        $pipeline = new FilterPipeline;

        // Create two mock filters that will modify the query in sequence
        $filter1 = $this->createMockFilter('filter1');
        $filter2 = $this->createMockFilter('filter2');

        // The first filter will add a where clause
        $filter1->expects($this->once())
            ->method('apply')
            ->willReturnCallback(function (Builder $query, $value) {
                return $query->where('salary_min', '>=', $value);
            });

        // The second filter will add a where clause that depends on the first one being applied
        $filter2->expects($this->once())
            ->method('apply')
            ->willReturnCallback(function (Builder $query, $value) {
                return $query->where('salary_max', '<=', $value);
            });

        $pipeline->addFilter($filter1);
        $pipeline->addFilter($filter2);

        // Create query and filters data
        $query = JobPost::query();
        $filterData = [
            'filter1' => 50000,
            'filter2' => 100000,
        ];

        // Process the filters
        $processedQuery = $pipeline->process($query, $filterData);

        // Get the query builder's 'wheres' property to check if both filters were applied in sequence
        $queryWheres = $processedQuery->getQuery()->wheres;

        $this->assertCount(2, $queryWheres);

        // Check first where clause
        $this->assertEquals('Basic', $queryWheres[0]['type']);
        $this->assertEquals('salary_min', $queryWheres[0]['column']);
        $this->assertEquals('>=', $queryWheres[0]['operator']);
        $this->assertEquals(50000, $queryWheres[0]['value']);

        // Check second where clause
        $this->assertEquals('Basic', $queryWheres[1]['type']);
        $this->assertEquals('salary_max', $queryWheres[1]['column']);
        $this->assertEquals('<=', $queryWheres[1]['operator']);
        $this->assertEquals(100000, $queryWheres[1]['value']);
    }

    public function test_pipeline_can_add_multiple_filters_fluently()
    {
        $pipeline = new FilterPipeline;

        $filter1 = $this->createMockFilter('filter1');
        $filter2 = $this->createMockFilter('filter2');
        $filter3 = $this->createMockFilter('filter3');

        // Chain the method calls
        $pipeline->addFilter($filter1)
            ->addFilter($filter2)
            ->addFilter($filter3);

        // Reflection to check if all filters were added correctly
        $reflection = new \ReflectionClass($pipeline);
        $property = $reflection->getProperty('filters');
        $property->setAccessible(true);

        $filters = $property->getValue($pipeline);

        $this->assertCount(3, $filters);
        $this->assertSame($filter1, $filters['filter1']);
        $this->assertSame($filter2, $filters['filter2']);
        $this->assertSame($filter3, $filters['filter3']);
    }

    private function createMockFilter(string $name)
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->method('getName')->willReturn($name);

        return $filter;
    }
}
