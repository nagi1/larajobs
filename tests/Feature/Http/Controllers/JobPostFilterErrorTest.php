<?php

namespace Tests\Feature\Http\Controllers;

use App\Filters\IsRemoteFilter;
use App\Filters\JobTypeFilter;
use App\Filters\StatusFilter;
use App\Models\JobPost;
use App\Services\Filters\LogicalFilterPipeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostFilterErrorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        JobPost::factory()->count(3)->create();

        // Set up the pipeline with required filters
        $pipeline = app(LogicalFilterPipeline::class);
        $pipeline->addFilter(new JobTypeFilter);
        $pipeline->addFilter(new IsRemoteFilter);
        $pipeline->addFilter(new StatusFilter);
        $this->app->instance(LogicalFilterPipeline::class, $pipeline);
    }

    public function test_index_handles_invalid_logical_operator_format(): void
    {
        // Send a request with an invalid filter format
        $response = $this->getJson('/api/jobs?filter[invalid_operator][0][job_type]=full-time');

        // Assert that we get a 400 Bad Request
        $response->assertStatus(400);

        // Assert that the response has the expected error structure
        $response->assertJsonStructure([
            'error',
            'message',
            'code',
        ]);

        // Assert that the error code is INVALID_FILTER
        $response->assertJson([
            'code' => 'INVALID_FILTER',
        ]);
    }

    public function test_index_handles_invalid_filter_condition_format(): void
    {
        // Send a request with an invalid condition format
        $response = $this->getJson('/api/jobs?filter[and]=not_an_array');

        // Assert that we get a 400 Bad Request
        $response->assertStatus(400);

        // Assert that the response has the expected error structure
        $response->assertJsonStructure([
            'error',
            'message',
            'code',
        ]);

        // Assert that the error message mentions array
        $response->assertJsonFragment([
            'message' => "Conditions for 'and' operation must be an array",
        ]);
    }

    public function test_index_handles_too_few_conditions(): void
    {
        // Send a request with only one condition
        $response = $this->getJson('/api/jobs?filter[and][0][job_type]=full-time');

        // Assert that we get a 400 Bad Request
        $response->assertStatus(400);

        // Assert that the response contains the expected error message
        $response->assertJsonFragment([
            'message' => "At least 2 conditions are required for 'and' operation",
        ]);
    }

    public function test_index_handles_both_and_or_operators(): void
    {
        // Send a request with both AND and OR operators at the same level
        $response = $this->getJson('/api/jobs?filter[and][0][job_type]=full-time&filter[and][1][is_remote]=1&filter[or][0][status]=published');

        // Assert that we get a 400 Bad Request
        $response->assertStatus(400);

        // Assert that the response contains the expected error message
        $response->assertJsonFragment([
            'message' => 'Cannot use both "and" and "or" operations at the same level',
        ]);
    }

    public function test_index_handles_invalid_condition_format(): void
    {
        // Send a request with a non-array condition
        $response = $this->getJson('/api/jobs?filter[and][0][job_type]=full-time&filter[and][1]=invalid');

        // Assert that we get a 400 Bad Request
        $response->assertStatus(400);

        // Assert that the response contains the expected error message
        $response->assertJsonFragment([
            'message' => 'Each condition must be an array',
        ]);
    }
}
