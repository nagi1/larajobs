<?php

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Filters\IsRemoteFilter;
use App\Filters\JobTypeFilter;
use App\Filters\SalaryRangeFilter;
use App\Filters\StatusFilter;
use App\Filters\TitleFilter;
use App\Models\JobPost;
use App\Services\Filters\LogicalFilterPipeline;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create our pipeline with real filters
    $this->pipeline = new LogicalFilterPipeline;

    // Add some common filters that we'll use in our tests
    $this->pipeline->addFilter(new JobTypeFilter);
    $this->pipeline->addFilter(new IsRemoteFilter);
    $this->pipeline->addFilter(new SalaryRangeFilter);
    $this->pipeline->addFilter(new StatusFilter);
    $this->pipeline->addFilter(new TitleFilter);

    // Create test job posts with different attributes
    // Full-time, remote, high salary job
    JobPost::factory()->create([
        'title' => 'Senior Developer',
        'job_type' => JobType::FULL_TIME,
        'is_remote' => true,
        'salary_min' => 100000,
        'salary_max' => 150000,
        'status' => JobStatus::PUBLISHED,
    ]);

    // Part-time, non-remote, medium salary job
    JobPost::factory()->create([
        'title' => 'Junior Developer',
        'job_type' => JobType::PART_TIME,
        'is_remote' => false,
        'salary_min' => 50000,
        'salary_max' => 70000,
        'status' => JobStatus::PUBLISHED,
    ]);

    // Contract, remote, medium salary job
    JobPost::factory()->create([
        'title' => 'Contract Developer',
        'job_type' => JobType::CONTRACT,
        'is_remote' => true,
        'salary_min' => 80000,
        'salary_max' => 90000,
        'status' => JobStatus::PUBLISHED,
    ]);

    // Freelance, remote, low salary job
    JobPost::factory()->create([
        'title' => 'Freelance Developer',
        'job_type' => JobType::FREELANCE,
        'is_remote' => true,
        'salary_min' => 40000,
        'salary_max' => 60000,
        'status' => JobStatus::ARCHIVED,
    ]);
});

test('filters jobs with and conditions', function () {
    // Filter for full-time, remote jobs
    $filterData = [
        'and' => [
            ['job_type' => JobType::FULL_TIME->value],
            ['is_remote' => true],
        ],
    ];

    $query = JobPost::query();
    $filteredQuery = $this->pipeline->process($query, $filterData);
    $results = $filteredQuery->get();

    // Should match only the Senior Developer job
    expect($results)->toHaveCount(1);
    expect($results->first()->title)->toBe('Senior Developer');
});

test('filters jobs with or conditions', function () {
    // Filter for full-time OR contract jobs
    $filterData = [
        'or' => [
            ['job_type' => JobType::FULL_TIME->value],
            ['job_type' => JobType::CONTRACT->value],
        ],
    ];

    $query = JobPost::query();
    $filteredQuery = $this->pipeline->process($query, $filterData);
    $results = $filteredQuery->get();

    // Should match the Senior Developer and Contract Developer jobs
    expect($results)->toHaveCount(2);
    expect($results->contains('title', 'Senior Developer'))->toBeTrue();
    expect($results->contains('title', 'Contract Developer'))->toBeTrue();
});

test('filters jobs with nested conditions', function () {
    // Filter for published jobs that are either:
    // - Remote AND high salary (>90000)
    // - Non-remote AND low salary (<60000)
    $filterData = [
        'and' => [
            ['status' => JobStatus::PUBLISHED->value],
            [
                'or' => [
                    [
                        'and' => [
                            ['is_remote' => true],
                            ['salary' => ['min' => 90000]],
                        ],
                    ],
                    [
                        'and' => [
                            ['is_remote' => false],
                            ['salary' => ['max' => 60000]],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $query = JobPost::query();
    $filteredQuery = $this->pipeline->process($query, $filterData);
    $results = $filteredQuery->get();

    // Should match only the Senior Developer job
    expect($results)->toHaveCount(1);
    expect($results->first()->title)->toBe('Senior Developer');
});

test('filters using title search with and conditions', function () {
    // Filter for remote jobs with "Developer" in the title
    $filterData = [
        'and' => [
            ['is_remote' => true],
            ['title' => 'Developer'],
        ],
    ];

    $query = JobPost::query();
    $filteredQuery = $this->pipeline->process($query, $filterData);
    $results = $filteredQuery->get();

    // Should match 3 remote developer jobs
    expect($results)->toHaveCount(3);
    expect($results->contains('title', 'Senior Developer'))->toBeTrue();
    expect($results->contains('title', 'Contract Developer'))->toBeTrue();
    expect($results->contains('title', 'Freelance Developer'))->toBeTrue();
});
