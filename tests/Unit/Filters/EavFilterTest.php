<?php

use App\Enums\AttributeType;
use App\Filters\EavFilter;
use App\Models\Attribute;
use App\Models\JobAttributeValue;
use App\Models\JobPost;

test('EavFilter can filter by text attribute', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'tech_stack',
        'type' => AttributeType::TEXT,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'Laravel, React, AWS',
    ]);

    // Create another job post with different tech stack
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => 'Python, Django, GCP',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'tech_stack',
        'operator' => '=',
        'value' => 'Laravel, React, AWS',
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by text attribute using LIKE operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'tech_stack',
        'type' => AttributeType::TEXT,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'Laravel, React, AWS',
    ]);

    // Create another job post with different tech stack
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => 'Python, Django, GCP',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with LIKE operator
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'tech_stack',
        'operator' => 'like',
        'value' => 'Laravel',
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter returns empty when no matches found', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'tech_stack',
        'type' => AttributeType::TEXT,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'Laravel, React, AWS',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with non-matching value
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'tech_stack',
        'operator' => '=',
        'value' => 'Non-existent Stack',
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(0);
});

test('EavFilter handles invalid attribute name', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'tech_stack',
        'type' => AttributeType::TEXT,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'Laravel, React, AWS',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with invalid attribute name
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'invalid_attribute',
        'operator' => '=',
        'value' => 'Laravel, React, AWS',
    ]);

    // Assert results (should return all jobs)
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter handles empty value', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'tech_stack',
        'type' => AttributeType::TEXT,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'Laravel, React, AWS',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with empty value
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, []);

    // Assert results (should return all jobs)
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});
