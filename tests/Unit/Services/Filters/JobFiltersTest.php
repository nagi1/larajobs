<?php

namespace Tests\Unit\Services\Filters;

use App\Enums\JobType;
use App\Models\JobPost;
use App\Services\Filters\JobTypeFilter;
use App\Services\Filters\RemoteFilter;
use App\Services\Filters\SalaryMaxFilter;
use App\Services\Filters\SalaryMinFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobFiltersTest extends TestCase
{
    use RefreshDatabase;

    private function createJobPost(array $attributes = []): JobPost
    {
        return JobPost::factory()->create($attributes);
    }

    public function test_job_type_filter()
    {
        // Create job posts with different job types
        $fullTimeJob = $this->createJobPost(['job_type' => JobType::FULL_TIME]);
        $partTimeJob = $this->createJobPost(['job_type' => JobType::PART_TIME]);
        $contractJob = $this->createJobPost(['job_type' => JobType::CONTRACT]);

        $filter = new JobTypeFilter;

        // Test single value filter
        $query = JobPost::query();
        $filteredQuery = $filter->apply($query, JobType::FULL_TIME->value);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertEquals($fullTimeJob->id, $results->first()->id);

        // Test array value filter
        $query = JobPost::query();
        $filteredQuery = $filter->apply($query, [JobType::FULL_TIME->value, JobType::PART_TIME->value]);
        $results = $filteredQuery->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $fullTimeJob->id));
        $this->assertTrue($results->contains('id', $partTimeJob->id));
        $this->assertFalse($results->contains('id', $contractJob->id));
    }

    public function test_remote_filter()
    {
        // Create job posts with different remote statuses
        $remoteJob = $this->createJobPost(['is_remote' => true]);
        $nonRemoteJob = $this->createJobPost(['is_remote' => false]);

        $filter = new RemoteFilter;

        // Test boolean true filter
        $query = JobPost::query();
        $filteredQuery = $filter->apply($query, true);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertEquals($remoteJob->id, $results->first()->id);

        // Test string value filter (should be converted to boolean)
        $query = JobPost::query();
        $filteredQuery = $filter->apply($query, 'true');
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertEquals($remoteJob->id, $results->first()->id);

        // Test boolean false filter
        $query = JobPost::query();
        $filteredQuery = $filter->apply($query, false);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertEquals($nonRemoteJob->id, $results->first()->id);
    }

    public function test_salary_min_filter()
    {
        // Create job posts with different salary ranges
        $lowSalaryJob = $this->createJobPost(['salary_min' => 50000, 'salary_max' => 80000]);
        $midSalaryJob = $this->createJobPost(['salary_min' => 80000, 'salary_max' => 120000]);
        $highSalaryJob = $this->createJobPost(['salary_min' => 120000, 'salary_max' => 200000]);

        $filter = new SalaryMinFilter;

        // Test filtering for jobs with salary_min >= 80000
        $query = JobPost::query();
        $filteredQuery = $filter->apply($query, 80000);
        $results = $filteredQuery->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $midSalaryJob->id));
        $this->assertTrue($results->contains('id', $highSalaryJob->id));
        $this->assertFalse($results->contains('id', $lowSalaryJob->id));

        // Test filtering for jobs with salary_min >= 100000
        $query = JobPost::query();
        $filteredQuery = $filter->apply($query, 100000);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $highSalaryJob->id));
    }

    public function test_salary_max_filter()
    {
        // Create job posts with different salary ranges
        $lowSalaryJob = $this->createJobPost(['salary_min' => 50000, 'salary_max' => 80000]);
        $midSalaryJob = $this->createJobPost(['salary_min' => 80000, 'salary_max' => 120000]);
        $highSalaryJob = $this->createJobPost(['salary_min' => 120000, 'salary_max' => 200000]);

        $filter = new SalaryMaxFilter;

        // Test filtering for jobs with salary_max <= 120000
        $query = JobPost::query();
        $filteredQuery = $filter->apply($query, 120000);
        $results = $filteredQuery->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $lowSalaryJob->id));
        $this->assertTrue($results->contains('id', $midSalaryJob->id));
        $this->assertFalse($results->contains('id', $highSalaryJob->id));

        // Test filtering for jobs with salary_max <= 100000
        $query = JobPost::query();
        $filteredQuery = $filter->apply($query, 100000);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $lowSalaryJob->id));
    }
}
