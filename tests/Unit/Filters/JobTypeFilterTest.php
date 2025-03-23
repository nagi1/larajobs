<?php

use App\Enums\JobType;
use App\Filters\JobTypeFilter;
use App\Models\JobPost;

test('job type filter can filter by single type', function () {
    $filter = new JobTypeFilter;

    // Create test data
    $fullTimeJob = JobPost::factory()->create(['job_type' => JobType::FULL_TIME]);
    $partTimeJob = JobPost::factory()->create(['job_type' => JobType::PART_TIME]);

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, JobType::FULL_TIME->value);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($fullTimeJob->id);
});

test('job type filter can filter by multiple types', function () {
    $filter = new JobTypeFilter;

    // Create test data
    $fullTimeJob = JobPost::factory()->create(['job_type' => JobType::FULL_TIME]);
    $partTimeJob = JobPost::factory()->create(['job_type' => JobType::PART_TIME]);
    $contractJob = JobPost::factory()->create(['job_type' => JobType::CONTRACT]);

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        JobType::FULL_TIME->value,
        JobType::PART_TIME->value,
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($fullTimeJob->id)
        ->toContain($partTimeJob->id);
});

test('job type filter ignores invalid types', function () {
    $filter = new JobTypeFilter;

    // Create test data
    $fullTimeJob = JobPost::factory()->create(['job_type' => JobType::FULL_TIME]);
    $partTimeJob = JobPost::factory()->create(['job_type' => JobType::PART_TIME]);

    // Apply filter with invalid type
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'invalid-type');

    // Assert results (should return all jobs)
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($fullTimeJob->id)
        ->toContain($partTimeJob->id);
});

test('job type filter handles empty value', function () {
    $filter = new JobTypeFilter;

    // Create test data
    $fullTimeJob = JobPost::factory()->create(['job_type' => JobType::FULL_TIME]);
    $partTimeJob = JobPost::factory()->create(['job_type' => JobType::PART_TIME]);

    // Apply filter with empty value
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, '');

    // Assert results (should return all jobs)
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($fullTimeJob->id)
        ->toContain($partTimeJob->id);
});
