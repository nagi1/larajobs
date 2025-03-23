<?php

use App\Filters\DescriptionFilter;
use App\Models\JobPost;

test('DescriptionFilter applies description search correctly', function () {
    // Create test data
    JobPost::factory()->create(['description' => 'Looking for a Laravel developer']);
    JobPost::factory()->create(['description' => 'React developer needed']);
    JobPost::factory()->create(['description' => 'Product management role']);

    // Create filter instance
    $filter = new DescriptionFilter('description', 'description');

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'Laravel');

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1);
    expect($results->first()->description)->toBe('Looking for a Laravel developer');
});

test('DescriptionFilter returns empty when no matches found', function () {
    // Create test data
    JobPost::factory()->create(['description' => 'Looking for a Laravel developer']);
    JobPost::factory()->create(['description' => 'React developer needed']);
    JobPost::factory()->create(['description' => 'Product management role']);

    // Create filter instance
    $filter = new DescriptionFilter('description', 'description');

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'NonExistentSkill');

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(0);
});
