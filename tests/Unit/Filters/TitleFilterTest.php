<?php

use App\Filters\TitleFilter;
use App\Models\JobPost;

test('TitleFilter applies title search correctly', function () {
    // Create test data
    JobPost::factory()->create(['title' => 'Senior Software Engineer']);
    JobPost::factory()->create(['title' => 'Junior Developer']);
    JobPost::factory()->create(['title' => 'Product Manager']);

    // Create filter instance
    $filter = new TitleFilter('title', 'title');

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'Software');

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1);
    expect($results->first()->title)->toBe('Senior Software Engineer');
});

test('TitleFilter returns empty when no matches found', function () {
    // Create test data
    JobPost::factory()->create(['title' => 'Senior Software Engineer']);
    JobPost::factory()->create(['title' => 'Junior Developer']);
    JobPost::factory()->create(['title' => 'Product Manager']);

    // Create filter instance
    $filter = new TitleFilter('title', 'title');

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'NonExistentTitle');

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(0);
});
