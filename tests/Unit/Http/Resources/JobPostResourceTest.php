<?php

namespace Tests\Unit\Http\Resources;

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Http\Resources\JobPostResource;
use App\Models\JobPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_contains_correct_data()
    {
        // Create a job post with known data
        $jobPost = JobPost::factory()->create([
            'title' => 'Test Job',
            'description' => 'Test Description',
            'company_name' => 'Test Company',
            'salary_min' => 50000,
            'salary_max' => 100000,
            'is_remote' => true,
            'job_type' => JobType::FULL_TIME,
            'status' => JobStatus::PUBLISHED,
        ]);

        // Create the resource from the job post
        $resource = new JobPostResource($jobPost);

        // Convert the resource to an array
        $resourceArray = $resource->toArray(request());

        // Assert the array contains the correct data
        $this->assertEquals($jobPost->id, $resourceArray['id']);
        $this->assertEquals('Test Job', $resourceArray['title']);
        $this->assertEquals('Test Description', $resourceArray['description']);
        $this->assertEquals('Test Company', $resourceArray['company_name']);
        $this->assertEquals(50000, $resourceArray['salary_min']);
        $this->assertEquals(100000, $resourceArray['salary_max']);
        $this->assertEquals(true, $resourceArray['is_remote']);
        $this->assertEquals(JobType::FULL_TIME, $resourceArray['job_type']);
        $this->assertEquals('full-time', $resourceArray['job_type_value']);
        $this->assertEquals(JobStatus::PUBLISHED, $resourceArray['status']);
        $this->assertEquals('published', $resourceArray['status_value']);
        $this->assertEquals($jobPost->published_at, $resourceArray['published_at']);
        $this->assertEquals($jobPost->created_at, $resourceArray['created_at']);
        $this->assertEquals($jobPost->updated_at, $resourceArray['updated_at']);
    }
}
