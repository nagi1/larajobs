<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Models\JobPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_successful_response(): void
    {
        // Create some job posts in the database
        JobPost::factory()->count(3)->create();

        // Make a request to the endpoint
        $response = $this->getJson('/api/jobs');

        // Assert that the response is successful
        $response->assertStatus(200);

        // Assert that the response has the expected structure
        $response->assertJsonStructure([
            'data',
            'meta' => [
                'total',
                'per_page',
                'current_page',
                'last_page',
                'from',
                'to',
            ],
        ]);

        // Assert that we have 3 job posts in the response
        $response->assertJsonCount(3, 'data');
    }

    public function test_index_filters_job_posts(): void
    {
        // Delete all existing records to have a clean slate
        JobPost::query()->delete();

        // Create job posts with different job types
        JobPost::factory()->count(3)->create([
            'job_type' => JobType::FULL_TIME,
            'status' => JobStatus::PUBLISHED, // Force status to be published
        ]);

        JobPost::factory()->count(2)->create([
            'job_type' => JobType::PART_TIME,
            'status' => JobStatus::PUBLISHED, // Force status to be published
            'is_remote' => false, // Force to NOT be remote
        ]);

        // Create a specific part-time remote job
        JobPost::factory()->create([
            'job_type' => JobType::PART_TIME,
            'is_remote' => true,
            'status' => JobStatus::PUBLISHED,
        ]);

        $total = JobPost::count();
        $fullTime = JobPost::where('job_type', JobType::FULL_TIME)->count();
        $partTime = JobPost::where('job_type', JobType::PART_TIME)->count();
        $partTimeRemote = JobPost::where('job_type', JobType::PART_TIME)
            ->where('is_remote', true)
            ->count();

        $this->assertEquals(6, $total, 'Total jobs should be 6');
        $this->assertEquals(3, $fullTime, 'Full-time jobs should be 3');
        $this->assertEquals(3, $partTime, 'Part-time jobs should be 3');
        $this->assertEquals(1, $partTimeRemote, 'Part-time remote jobs should be 1');

        // Test filtering by job type
        $response = $this->getJson('/api/jobs?filter=job_type=full-time');

        // Assert successful response
        $response->assertStatus(200);

        // Assert that only full-time jobs are returned
        $response->assertJsonCount(3, 'data');

        // Test filtering by job status
        $response = $this->getJson('/api/jobs?filter=status=published');

        // Assert that all published jobs are returned (6 total)
        $response->assertJsonCount(6, 'data');

        // Test filtering for part-time jobs
        $response = $this->getJson('/api/jobs?filter=job_type=part-time');

        // We should have 3 part-time jobs
        $response->assertJsonCount(3, 'data');

        // Test filtering for remote jobs
        $response = $this->getJson('/api/jobs?filter=is_remote=true');

        // Count all remote jobs in the database
        $remoteCount = JobPost::where('is_remote', true)->count();

        // Check that the response has the correct count
        $response->assertJsonCount($remoteCount, 'data');
    }

    public function test_index_sorts_and_paginates_job_posts(): void
    {
        // Create job posts with different salary ranges
        JobPost::factory()->count(3)->create([
            'salary_min' => 80000,
        ]);

        JobPost::factory()->count(3)->create([
            'salary_min' => 60000,
        ]);

        JobPost::factory()->count(3)->create([
            'salary_min' => 40000,
        ]);

        // Test sorting by salary_min in ascending order
        $response = $this->getJson('/api/jobs?sort=salary_min&order=asc&per_page=5');

        // Assert that response is successful
        $response->assertStatus(200);

        // Assert pagination is working
        $response->assertJsonPath('meta.per_page', 5);
        $response->assertJsonCount(5, 'data');

        // Get the data from the response
        $data = $response->json('data');

        // Assert sorting is working
        $this->assertLessThanOrEqual($data[1]['salary_min'], $data[2]['salary_min']);
        $this->assertLessThanOrEqual($data[0]['salary_min'], $data[1]['salary_min']);

        // Test sorting by salary_min in descending order
        $response = $this->getJson('/api/jobs?sort=salary_min&order=desc&per_page=5');

        // Get the data from the response
        $data = $response->json('data');

        // Assert sorting is working in descending order
        $this->assertGreaterThanOrEqual($data[1]['salary_min'], $data[2]['salary_min']);
        $this->assertGreaterThanOrEqual($data[0]['salary_min'], $data[1]['salary_min']);
    }
}
