<?php

use App\Filters\IsRemoteFilter;
use App\Models\JobPost;

test('IsRemoteFilter filters remote jobs correctly with boolean true', function () {
    // Create test data
    JobPost::factory()->create(['is_remote' => true]);
    JobPost::factory()->create(['is_remote' => false]);
    JobPost::factory()->create(['is_remote' => false]);

    // Create filter instance
    $filter = new IsRemoteFilter('is_remote', 'is_remote');

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, true);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1);
    expect($results->first()->is_remote)->toBeTrue();
});

test('IsRemoteFilter filters non-remote jobs correctly with boolean false', function () {
    // Create test data
    JobPost::factory()->create(['is_remote' => true]);
    JobPost::factory()->create(['is_remote' => false]);
    JobPost::factory()->create(['is_remote' => false]);

    // Create filter instance
    $filter = new IsRemoteFilter('is_remote', 'is_remote');

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, false);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2);
    expect($results->every(fn ($job) => $job->is_remote === false))->toBeTrue();
});

test('IsRemoteFilter handles string boolean values correctly', function () {
    // Create test data
    JobPost::factory()->create(['is_remote' => true]);
    JobPost::factory()->create(['is_remote' => false]);

    // Create filter instance
    $filter = new IsRemoteFilter('is_remote', 'is_remote');

    // Test 'true' string
    $query = JobPost::query();
    $results = $filter->apply($query, 'true')->get();
    expect($results)->toHaveCount(1);
    expect($results->first()->is_remote)->toBeTrue();

    // Test '1' string
    $query = JobPost::query();
    $results = $filter->apply($query, '1')->get();
    expect($results)->toHaveCount(1);
    expect($results->first()->is_remote)->toBeTrue();
});

test('IsRemoteFilter returns all results for invalid value', function () {
    // Create test data
    JobPost::factory()->create(['is_remote' => true]);
    JobPost::factory()->create(['is_remote' => false]);
    JobPost::factory()->create(['is_remote' => false]);

    // Create filter instance
    $filter = new IsRemoteFilter('is_remote', 'is_remote');

    // Apply filter with invalid value
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'invalid');

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(3);
});
