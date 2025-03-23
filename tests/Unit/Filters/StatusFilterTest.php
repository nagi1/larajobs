<?php

use App\Enums\JobStatus;
use App\Filters\StatusFilter;
use App\Models\JobPost;

test('status filter can filter by single status', function () {
    $filter = new StatusFilter;

    // Create test data
    $publishedJob = JobPost::factory()->create(['status' => JobStatus::PUBLISHED]);
    $draftJob = JobPost::factory()->create(['status' => JobStatus::DRAFT]);

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, JobStatus::PUBLISHED->value);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($publishedJob->id);
});

test('status filter can filter by multiple statuses', function () {
    $filter = new StatusFilter;

    // Create test data
    $publishedJob = JobPost::factory()->create(['status' => JobStatus::PUBLISHED]);
    $draftJob = JobPost::factory()->create(['status' => JobStatus::DRAFT]);
    $archivedJob = JobPost::factory()->create(['status' => JobStatus::ARCHIVED]);

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        JobStatus::PUBLISHED->value,
        JobStatus::DRAFT->value,
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($publishedJob->id)
        ->toContain($draftJob->id);
});

test('status filter ignores invalid statuses', function () {
    $filter = new StatusFilter;

    // Create test data
    $publishedJob = JobPost::factory()->create(['status' => JobStatus::PUBLISHED]);
    $draftJob = JobPost::factory()->create(['status' => JobStatus::DRAFT]);

    // Apply filter with invalid status
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'invalid-status');

    // Assert results (should return all jobs)
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($publishedJob->id)
        ->toContain($draftJob->id);
});

test('status filter handles empty value', function () {
    $filter = new StatusFilter;

    // Create test data
    $publishedJob = JobPost::factory()->create(['status' => JobStatus::PUBLISHED]);
    $draftJob = JobPost::factory()->create(['status' => JobStatus::DRAFT]);

    // Apply filter with empty value
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, '');

    // Assert results (should return all jobs)
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($publishedJob->id)
        ->toContain($draftJob->id);
});
