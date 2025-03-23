<?php

use App\Filters\SalaryRangeFilter;
use App\Models\JobPost;

test('SalaryRangeFilter applies minimum salary filter correctly', function () {
    // Create test data
    JobPost::factory()->create(['salary_min' => 50000, 'salary_max' => 70000]);
    JobPost::factory()->create(['salary_min' => 30000, 'salary_max' => 45000]);
    JobPost::factory()->create(['salary_min' => 80000, 'salary_max' => 100000]);

    // Create filter instance
    $filter = new SalaryRangeFilter('salary', 'salary');

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, ['min' => 60000]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1);
    expect($results->first()->salary_min)->toEqual(80000.00);
});

test('SalaryRangeFilter applies maximum salary filter correctly', function () {
    // Create test data
    JobPost::factory()->create(['salary_min' => 50000, 'salary_max' => 70000]);
    JobPost::factory()->create(['salary_min' => 30000, 'salary_max' => 45000]);
    JobPost::factory()->create(['salary_min' => 80000, 'salary_max' => 100000]);

    // Create filter instance
    $filter = new SalaryRangeFilter('salary', 'salary');

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, ['max' => 60000]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1);
    expect($results->first()->salary_max)->toEqual(45000.00);
});

test('SalaryRangeFilter applies both min and max salary filters correctly', function () {
    // Create test data
    JobPost::factory()->create(['salary_min' => 50000, 'salary_max' => 70000]);
    JobPost::factory()->create(['salary_min' => 30000, 'salary_max' => 45000]);
    JobPost::factory()->create(['salary_min' => 80000, 'salary_max' => 100000]);

    // Create filter instance
    $filter = new SalaryRangeFilter('salary', 'salary');

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'min' => 40000,
        'max' => 75000,
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1);
    expect($results->first()->salary_min)->toEqual(50000.00);
    expect($results->first()->salary_max)->toEqual(70000.00);
});

test('SalaryRangeFilter returns all results when no filters provided', function () {
    // Create test data
    JobPost::factory()->create(['salary_min' => 50000, 'salary_max' => 70000]);
    JobPost::factory()->create(['salary_min' => 30000, 'salary_max' => 45000]);
    JobPost::factory()->create(['salary_min' => 80000, 'salary_max' => 100000]);

    // Create filter instance
    $filter = new SalaryRangeFilter('salary', 'salary');

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, []);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(3);
});
