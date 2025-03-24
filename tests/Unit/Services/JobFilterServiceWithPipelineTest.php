<?php

namespace Tests\Unit\Services;

use App\Contracts\Filters\FilterInterface;
use App\Contracts\Filters\PipelineInterface;
use App\Models\JobPost;
use App\Services\Filters\FilterPipeline;
use App\Services\JobFilterService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class JobFilterServiceWithPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_service_uses_pipeline_for_array_filters()
    {
        // Create mock pipeline and filters
        $mockPipeline = Mockery::mock(PipelineInterface::class);
        $mockQuery = Mockery::mock(Builder::class);

        // Expected filter data
        $filterData = ['job_type' => 'full-time', 'is_remote' => true];

        // Setup pipeline expectations
        $mockPipeline->shouldReceive('process')
            ->once()
            ->with($mockQuery, $filterData)
            ->andReturn($mockQuery);

        // Create service with mock pipeline
        $service = new JobFilterService($mockPipeline);

        // Test that pipeline is used when array filter data is provided
        $result = $service->apply($mockQuery, $filterData);

        // Verify result
        $this->assertSame($mockQuery, $result);
    }

    public function test_service_uses_legacy_parsing_for_string_filters()
    {
        // Create mock pipeline
        $mockPipeline = Mockery::mock(PipelineInterface::class);

        // Make sure pipeline process is not called
        $mockPipeline->shouldNotReceive('process');

        // Create a real filter service with the mock pipeline
        $service = new JobFilterService($mockPipeline);

        // Create a simple query
        $query = JobPost::query();

        // We'll have a real job post to test with
        $jobPost = JobPost::factory()->create([
            'job_type' => 'full-time',
            'is_remote' => true,
        ]);

        // Test with a simple string filter that should work
        $result = $service->apply($query, 'job_type=full-time');

        // Verify the result has the expected record
        $this->assertCount(1, $result->get());
        $this->assertEquals($jobPost->id, $result->first()->id);
    }

    public function test_integration_with_real_filter_pipeline()
    {
        // Create job posts
        $fullTimeRemote = JobPost::factory()->create([
            'job_type' => 'full-time',
            'is_remote' => true,
            'salary_min' => 80000,
            'salary_max' => 120000,
        ]);

        $partTimeNonRemote = JobPost::factory()->create([
            'job_type' => 'part-time',
            'is_remote' => false,
            'salary_min' => 40000,
            'salary_max' => 60000,
        ]);

        // Create concrete filters
        $jobTypeFilter = Mockery::mock(FilterInterface::class);
        $jobTypeFilter->shouldReceive('getName')->andReturn('job_type');
        $jobTypeFilter->shouldReceive('apply')
            ->with(Mockery::type(Builder::class), 'full-time')
            ->andReturnUsing(function (Builder $query, $value) {
                return $query->where('job_type', $value);
            });

        $remoteFilter = Mockery::mock(FilterInterface::class);
        $remoteFilter->shouldReceive('getName')->andReturn('is_remote');
        $remoteFilter->shouldReceive('apply')
            ->with(Mockery::type(Builder::class), true)
            ->andReturnUsing(function (Builder $query, $value) {
                return $query->where('is_remote', $value);
            });

        // Create pipeline with filters
        $pipeline = new FilterPipeline;
        $pipeline->addFilter($jobTypeFilter);
        $pipeline->addFilter($remoteFilter);

        // Create service with pipeline
        $service = new JobFilterService($pipeline);

        // Test filtering
        $query = JobPost::query();
        $result = $service->apply($query, [
            'job_type' => 'full-time',
            'is_remote' => true,
        ]);

        // Check results
        $this->assertCount(1, $result->get());
        $this->assertEquals($fullTimeRemote->id, $result->first()->id);
    }
}
