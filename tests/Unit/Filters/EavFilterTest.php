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

test('EavFilter can filter by number attribute with greater than operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'years_of_experience',
        'type' => AttributeType::NUMBER,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '5',
    ]);

    // Create another job post with different experience
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => '2',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with greater than operator
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'years_of_experience',
        'operator' => '>',
        'value' => '3',
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by number attribute with less than operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'years_of_experience',
        'type' => AttributeType::NUMBER,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '2',
    ]);

    // Create another job post with different experience
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => '5',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with less than operator
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'years_of_experience',
        'operator' => '<',
        'value' => '3',
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by number attribute with equals operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'years_of_experience',
        'type' => AttributeType::NUMBER,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '5',
    ]);

    // Create another job post with different experience
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => '2',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with equals operator
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'years_of_experience',
        'operator' => '=',
        'value' => '5',
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by number attribute with greater than or equal operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'years_of_experience',
        'type' => AttributeType::NUMBER,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '5',
    ]);

    // Create another job post with different experience
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => '2',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with greater than or equal operator
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'years_of_experience',
        'operator' => '>=',
        'value' => '5',
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by number attribute with less than or equal operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'years_of_experience',
        'type' => AttributeType::NUMBER,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '2',
    ]);

    // Create another job post with different experience
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => '5',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with less than or equal operator
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'years_of_experience',
        'operator' => '<=',
        'value' => '2',
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by boolean attribute with true value', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'has_health_insurance',
        'type' => AttributeType::BOOLEAN,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '1',
    ]);

    // Create another job post with different value
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => '0',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with true value
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'has_health_insurance',
        'operator' => '=',
        'value' => true,
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by boolean attribute with false value', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'has_health_insurance',
        'type' => AttributeType::BOOLEAN,
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => false,
    ]);

    // Create another job post with different value
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => true,
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with false value
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'has_health_insurance',
        'operator' => '=',
        'value' => false,
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by select attribute with exact match', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'job_type',
        'type' => AttributeType::SELECT,
        'options' => ['Full-time', 'Part-time', 'Contract', 'Internship'],
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'Full-time',
    ]);

    // Create another job post with different job type
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => 'Part-time',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with exact match
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'job_type',
        'operator' => '=',
        'value' => 'Full-time',
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by select attribute with case-insensitive match', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'job_type',
        'type' => AttributeType::SELECT,
        'options' => ['Full-time', 'Part-time', 'Contract', 'Internship'],
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'Full-time',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with case-insensitive match
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'job_type',
        'operator' => '=',
        'value' => 'full-time',
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by select attribute with multiple values using IN operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'job_type',
        'type' => AttributeType::SELECT,
        'options' => ['Full-time', 'Part-time', 'Contract', 'Internship'],
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'Full-time',
    ]);

    // Create another job post with different job type
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => 'Part-time',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with multiple values
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'job_type',
        'operator' => 'in',
        'value' => ['Full-time', 'Part-time'],
    ]);

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->contains($jobPost->id))->toBeTrue()
        ->and($results->pluck('id')->contains($otherJobPost->id))->toBeTrue();
});

test('EavFilter handles invalid values in select attribute filter', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'job_type',
        'type' => AttributeType::SELECT,
        'options' => ['Full-time', 'Part-time', 'Contract', 'Internship'],
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'Full-time',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with invalid value
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'job_type',
        'operator' => '=',
        'value' => 'Invalid-Type',
    ]);

    // Assert results (should return empty)
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(0);
});

test('EavFilter handles mix of valid and invalid values in select attribute filter', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'job_type',
        'type' => AttributeType::SELECT,
        'options' => ['Full-time', 'Part-time', 'Contract', 'Internship'],
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'Full-time',
    ]);

    // Create another job post with different job type
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => 'Part-time',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with mix of valid and invalid values
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'job_type',
        'operator' => 'in',
        'value' => ['Full-time', 'Invalid-Type', 'Part-time'],
    ]);

    // Assert results (should only include valid values)
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->contains($jobPost->id))->toBeTrue()
        ->and($results->pluck('id')->contains($otherJobPost->id))->toBeTrue();
});

test('EavFilter can filter by date attribute with equals operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'start_date',
        'type' => AttributeType::DATE,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-15 00:00:00',
    ]);

    // Create another job post with different date
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-20 00:00:00',
    ]);

    $filter = new EavFilter;

    // Test exact match
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'start_date',
        'operator' => '=',
        'value' => '2024-03-15', // Should match even without time part
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by date attribute with greater than operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'start_date',
        'type' => AttributeType::DATE,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-15 00:00:00',
    ]);

    // Create another job post with later date
    $laterJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $laterJobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-20 00:00:00',
    ]);

    $filter = new EavFilter;

    // Test greater than
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'start_date',
        'operator' => '>',
        'value' => '2024-03-15', // Should work with just the date part
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($laterJobPost->id);
});

test('EavFilter can filter by date attribute with less than operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'start_date',
        'type' => AttributeType::DATE,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-15 00:00:00',
    ]);

    // Create another job post with later date
    $laterJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $laterJobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-20 00:00:00',
    ]);

    $filter = new EavFilter;

    // Test less than
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'start_date',
        'operator' => '<',
        'value' => '2024-03-20', // Should work with just the date part
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter handles invalid date values', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'start_date',
        'type' => AttributeType::DATE,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-15',
    ]);

    $filter = new EavFilter;

    // Test invalid date
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'start_date',
        'operator' => '=',
        'value' => 'invalid-date',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(0);
});

test('EavFilter can filter by date attribute with different date formats', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'start_date',
        'type' => AttributeType::DATE,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-15 00:00:00',
    ]);

    $filter = new EavFilter;

    // Test different date formats
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'start_date',
        'operator' => '=',
        'value' => '15-03-2024 00:00:00', // DD-MM-YYYY format with time
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);

    // Test with another format
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'start_date',
        'operator' => '=',
        'value' => 'March 15, 2024 12:00:00', // Human readable format with time
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by date attribute with greater than or equal operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'start_date',
        'type' => AttributeType::DATE,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-15 00:00:00',
    ]);

    // Create another job post with later date
    $laterJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $laterJobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-20 00:00:00',
    ]);

    $filter = new EavFilter;

    // Test greater than or equal
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'start_date',
        'operator' => '>=',
        'value' => '2024-03-15',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->contains($jobPost->id))->toBeTrue()
        ->and($results->pluck('id')->contains($laterJobPost->id))->toBeTrue();
});

test('EavFilter can filter by date attribute with less than or equal operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'start_date',
        'type' => AttributeType::DATE,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-15 00:00:00',
    ]);

    // Create another job post with later date
    $laterJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $laterJobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-20 00:00:00',
    ]);

    $filter = new EavFilter;

    // Test less than or equal
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'start_date',
        'operator' => '<=',
        'value' => '2024-03-20',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->contains($jobPost->id))->toBeTrue()
        ->and($results->pluck('id')->contains($laterJobPost->id))->toBeTrue();
});

test('EavFilter can filter by text attribute with inequality operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'tech_stack',
        'type' => AttributeType::TEXT,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'PHP, Laravel, MySQL',
    ]);

    // Create another job post with different tech stack
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => 'Python, Django, PostgreSQL',
    ]);

    $filter = new EavFilter;

    // Test inequality
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'tech_stack',
        'operator' => '!=',
        'value' => 'PHP, Laravel, MySQL',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($otherJobPost->id);
});

test('EavFilter can filter by number attribute with inequality operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'years_experience',
        'type' => AttributeType::NUMBER,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '5',
    ]);

    // Create another job post with different experience
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => '3',
    ]);

    $filter = new EavFilter;

    // Test inequality
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'years_experience',
        'operator' => '!=',
        'value' => '5',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($otherJobPost->id);
});

test('EavFilter can filter by boolean attribute with inequality operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'has_health_insurance',
        'type' => AttributeType::BOOLEAN,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '1',
    ]);

    // Create another job post with different value
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => '0',
    ]);

    $filter = new EavFilter;

    // Test inequality with true value
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'has_health_insurance',
        'operator' => '!=',
        'value' => true,
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($otherJobPost->id);

    // Test inequality with false value
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'has_health_insurance',
        'operator' => '!=',
        'value' => false,
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobPost->id);
});

test('EavFilter can filter by select attribute with inequality operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'job_type',
        'type' => AttributeType::SELECT,
        'options' => ['Full-time', 'Part-time', 'Contract', 'Internship'],
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'Full-time',
    ]);

    // Create another job post with different job type
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => 'Part-time',
    ]);

    $filter = new EavFilter;

    // Test inequality
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'job_type',
        'operator' => '!=',
        'value' => 'Full-time',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($otherJobPost->id);

    // Test case-insensitive inequality
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'job_type',
        'operator' => '!=',
        'value' => 'full-time',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($otherJobPost->id);
});

test('EavFilter can filter by date attribute with inequality operator', function () {
    // Create test data
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'start_date',
        'type' => AttributeType::DATE,
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-15 00:00:00',
    ]);

    // Create another job post with different date
    $otherJobPost = JobPost::factory()->create();
    JobAttributeValue::factory()->create([
        'job_post_id' => $otherJobPost,
        'attribute_id' => $attribute,
        'value' => '2024-03-20 00:00:00',
    ]);

    $filter = new EavFilter;

    // Test inequality with exact date
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'start_date',
        'operator' => '!=',
        'value' => '2024-03-15',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($otherJobPost->id);

    // Test inequality with different date format
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'start_date',
        'operator' => '!=',
        'value' => '15-03-2024',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($otherJobPost->id);
});

test('EavFilter can filter by number attribute with range (min and max)', function () {
    // Create test data
    $lowExpJobPost = JobPost::factory()->create();
    $midExpJobPost = JobPost::factory()->create();
    $highExpJobPost = JobPost::factory()->create();

    // Create experience attribute
    $attribute = Attribute::factory()->create([
        'name' => 'years_of_experience',
        'type' => AttributeType::NUMBER,
    ]);

    // Set different experience levels
    JobAttributeValue::factory()->create([
        'job_post_id' => $lowExpJobPost,
        'attribute_id' => $attribute,
        'value' => '2',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $midExpJobPost,
        'attribute_id' => $attribute,
        'value' => '5',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $highExpJobPost,
        'attribute_id' => $attribute,
        'value' => '10',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with range (min and max)
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'years_of_experience',
        'value' => [
            'min' => 3,
            'max' => 7,
        ],
    ]);

    // Assert results - should only match the midExpJobPost
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($midExpJobPost->id);
});

test('EavFilter can filter by number attribute with only min value', function () {
    // Create test data
    $lowExpJobPost = JobPost::factory()->create();
    $midExpJobPost = JobPost::factory()->create();
    $highExpJobPost = JobPost::factory()->create();

    // Create experience attribute
    $attribute = Attribute::factory()->create([
        'name' => 'years_of_experience',
        'type' => AttributeType::NUMBER,
    ]);

    // Set different experience levels
    JobAttributeValue::factory()->create([
        'job_post_id' => $lowExpJobPost,
        'attribute_id' => $attribute,
        'value' => '2',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $midExpJobPost,
        'attribute_id' => $attribute,
        'value' => '5',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $highExpJobPost,
        'attribute_id' => $attribute,
        'value' => '10',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with only min value
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'years_of_experience',
        'value' => [
            'min' => 5,
        ],
    ]);

    // Debug: Show the data
    $results = $filteredQuery->get();

    // Debug output
    echo "\nFilter with min=5, got ".$results->count()." results\n";
    foreach ($results as $job) {
        echo 'Job ID: '.$job->id."\n";
        $value = JobAttributeValue::where('job_post_id', $job->id)
            ->where('attribute_id', $attribute->id)
            ->value('value');
        echo '  Experience: '.$value."\n";
    }

    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())->toContain($midExpJobPost->id, $highExpJobPost->id);
});

test('EavFilter can filter by number attribute with only max value', function () {
    // Create test data
    $lowExpJobPost = JobPost::factory()->create();
    $midExpJobPost = JobPost::factory()->create();
    $highExpJobPost = JobPost::factory()->create();

    // Create experience attribute
    $attribute = Attribute::factory()->create([
        'name' => 'years_of_experience',
        'type' => AttributeType::NUMBER,
    ]);

    // Set different experience levels
    JobAttributeValue::factory()->create([
        'job_post_id' => $lowExpJobPost,
        'attribute_id' => $attribute,
        'value' => '2',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $midExpJobPost,
        'attribute_id' => $attribute,
        'value' => '5',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $highExpJobPost,
        'attribute_id' => $attribute,
        'value' => '10',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with only max value
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'years_of_experience',
        'value' => [
            'max' => 5,
        ],
    ]);

    // Debug: Show the data
    $results = $filteredQuery->get();

    // Debug output
    echo "\nFilter with max=5, got ".$results->count()." results\n";
    foreach ($results as $job) {
        echo 'Job ID: '.$job->id."\n";
        $value = JobAttributeValue::where('job_post_id', $job->id)
            ->where('attribute_id', $attribute->id)
            ->value('value');
        echo '  Experience: '.$value."\n";
    }

    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())->toContain($lowExpJobPost->id, $midExpJobPost->id);
});

test('EavFilter can filter by date attribute with date range', function () {
    // Create test data with different publishing dates
    $earlyJob = JobPost::factory()->create();
    $middleJob = JobPost::factory()->create();
    $recentJob = JobPost::factory()->create();

    // Create publishing date attribute
    $attribute = Attribute::factory()->create([
        'name' => 'publishing_date',
        'type' => AttributeType::DATE,
    ]);

    // Set different dates
    JobAttributeValue::factory()->create([
        'job_post_id' => $earlyJob,
        'attribute_id' => $attribute,
        'value' => '2023-01-15',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $middleJob,
        'attribute_id' => $attribute,
        'value' => '2023-06-25',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $recentJob,
        'attribute_id' => $attribute,
        'value' => '2023-12-10',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with date range
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'publishing_date',
        'value' => [
            'from' => '2023-04-01',
            'to' => '2023-10-31',
        ],
    ]);

    // Assert results - should only match the middleJob
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($middleJob->id);
});

test('EavFilter can filter using after/before instead of from/to', function () {
    // Create test data with different publishing dates
    $earlyJob = JobPost::factory()->create();
    $middleJob = JobPost::factory()->create();
    $recentJob = JobPost::factory()->create();

    // Create publishing date attribute
    $attribute = Attribute::factory()->create([
        'name' => 'publishing_date',
        'type' => AttributeType::DATE,
    ]);

    // Set different dates
    JobAttributeValue::factory()->create([
        'job_post_id' => $earlyJob,
        'attribute_id' => $attribute,
        'value' => '2023-01-15',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $middleJob,
        'attribute_id' => $attribute,
        'value' => '2023-06-25',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $recentJob,
        'attribute_id' => $attribute,
        'value' => '2023-12-10',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with after/before date constraints
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'publishing_date',
        'value' => [
            'after' => '2023-01-31',
            'before' => '2023-12-01',
        ],
    ]);

    // Assert results - should match the middleJob
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($middleJob->id);
});

test('EavFilter can filter by relative date term "this_month"', function () {
    // Create test data with different publishing dates
    $lastMonthJob = JobPost::factory()->create();
    $thisMonthJob = JobPost::factory()->create();
    $nextMonthJob = JobPost::factory()->create();

    // Create publishing date attribute
    $attribute = Attribute::factory()->create([
        'name' => 'publishing_date',
        'type' => AttributeType::DATE,
    ]);

    // Calculate relative dates based on current date
    $now = new DateTime;
    $lastMonth = (clone $now)->modify('first day of last month');
    $thisMonth = (clone $now)->modify('first day of this month')->modify('+5 days');
    $nextMonth = (clone $now)->modify('first day of next month');

    // Set different dates
    JobAttributeValue::factory()->create([
        'job_post_id' => $lastMonthJob,
        'attribute_id' => $attribute,
        'value' => $lastMonth->format('Y-m-d'),
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $thisMonthJob,
        'attribute_id' => $attribute,
        'value' => $thisMonth->format('Y-m-d'),
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $nextMonthJob,
        'attribute_id' => $attribute,
        'value' => $nextMonth->format('Y-m-d'),
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with relative date term
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'publishing_date',
        'value' => 'this_month',
    ]);

    // Assert results - should match only thisMonthJob
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($thisMonthJob->id);
});

test('EavFilter can filter by text attribute with contains mode', function () {
    // Create test data with different job descriptions
    $jobWithPHP = JobPost::factory()->create();
    $jobWithJS = JobPost::factory()->create();
    $jobWithPython = JobPost::factory()->create();

    // Create description attribute
    $attribute = Attribute::factory()->create([
        'name' => 'description',
        'type' => AttributeType::TEXT,
    ]);

    // Set different descriptions
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobWithPHP,
        'attribute_id' => $attribute,
        'value' => 'Looking for a PHP developer with Laravel experience',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $jobWithJS,
        'attribute_id' => $attribute,
        'value' => 'JavaScript developer needed for web project',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $jobWithPython,
        'attribute_id' => $attribute,
        'value' => 'Python developer for data science project',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with contains search mode
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'description',
        'value' => [
            'text' => 'developer',
            'mode' => 'contains',
        ],
    ]);

    // Assert results - should match all jobs
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(3);

    // Apply more specific filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'description',
        'value' => [
            'text' => 'PHP',
            'mode' => 'contains',
        ],
    ]);

    // Assert results - should only match PHP job
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobWithPHP->id);
});

test('EavFilter can filter by text attribute with starts_with mode', function () {
    // Create test data
    $jobStartsWithLooking = JobPost::factory()->create();
    $jobStartsWithJavaScript = JobPost::factory()->create();
    $jobStartsWithPython = JobPost::factory()->create();

    // Create description attribute
    $attribute = Attribute::factory()->create([
        'name' => 'description',
        'type' => AttributeType::TEXT,
    ]);

    // Set different descriptions
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobStartsWithLooking,
        'attribute_id' => $attribute,
        'value' => 'Looking for a PHP developer with Laravel experience',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $jobStartsWithJavaScript,
        'attribute_id' => $attribute,
        'value' => 'JavaScript developer needed for web project',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $jobStartsWithPython,
        'attribute_id' => $attribute,
        'value' => 'Python developer for data science project',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with starts_with search mode
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'description',
        'value' => [
            'text' => 'Java',
            'mode' => 'starts_with',
        ],
    ]);

    // Assert results - should only match JavaScript job
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobStartsWithJavaScript->id);
});

test('EavFilter can filter by text attribute with ends_with mode', function () {
    // Create test data
    $jobEndsWithExperience = JobPost::factory()->create();
    $jobEndsWithProject = JobPost::factory()->create();
    $jobEndsWithAnother = JobPost::factory()->create();

    // Create description attribute
    $attribute = Attribute::factory()->create([
        'name' => 'description',
        'type' => AttributeType::TEXT,
    ]);

    // Set different descriptions
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobEndsWithExperience,
        'attribute_id' => $attribute,
        'value' => 'Looking for a PHP developer with Laravel experience',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $jobEndsWithProject,
        'attribute_id' => $attribute,
        'value' => 'JavaScript developer needed for web project',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $jobEndsWithAnother,
        'attribute_id' => $attribute,
        'value' => 'Another job description',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with ends_with search mode
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'description',
        'value' => [
            'text' => 'experience',
            'mode' => 'ends_with',
        ],
    ]);

    // Assert results - should only match experience job
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobEndsWithExperience->id);
});

test('EavFilter can filter by text attribute with exact mode', function () {
    // Create test data
    $job1 = JobPost::factory()->create();
    $job2 = JobPost::factory()->create();
    $job3 = JobPost::factory()->create();

    // Create keyword attribute
    $attribute = Attribute::factory()->create([
        'name' => 'keyword',
        'type' => AttributeType::TEXT,
    ]);

    // Set different keywords
    JobAttributeValue::factory()->create([
        'job_post_id' => $job1,
        'attribute_id' => $attribute,
        'value' => 'Senior Developer',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $job2,
        'attribute_id' => $attribute,
        'value' => 'Senior Developer Position',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $job3,
        'attribute_id' => $attribute,
        'value' => 'Developer',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with exact search mode
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'keyword',
        'value' => [
            'text' => 'Senior Developer',
            'mode' => 'exact',
        ],
    ]);

    // Assert results - should only match exact "Senior Developer"
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($job1->id);
});

test('EavFilter can filter by text attribute with not_contains mode', function () {
    // Create test data
    $jobWithPHP = JobPost::factory()->create();
    $jobWithJS = JobPost::factory()->create();
    $jobWithPython = JobPost::factory()->create();

    // Create description attribute
    $attribute = Attribute::factory()->create([
        'name' => 'description',
        'type' => AttributeType::TEXT,
    ]);

    // Set different descriptions
    JobAttributeValue::factory()->create([
        'job_post_id' => $jobWithPHP,
        'attribute_id' => $attribute,
        'value' => 'Looking for a PHP developer with Laravel experience',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $jobWithJS,
        'attribute_id' => $attribute,
        'value' => 'JavaScript developer needed for web project',
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $jobWithPython,
        'attribute_id' => $attribute,
        'value' => 'Python developer for data science project',
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with not_contains search mode
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'description',
        'value' => [
            'text' => 'web',
            'mode' => 'not_contains',
        ],
    ]);

    // Assert results - should match PHP and Python jobs but not JS job
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())->toContain($jobWithPHP->id, $jobWithPython->id)
        ->and($results->pluck('id')->toArray())->not->toContain($jobWithJS->id);
});

test('EavFilter can filter by select attribute with any mode', function () {
    // Create test data with different skills
    $phpDeveloper = JobPost::factory()->create();
    $javaDeveloper = JobPost::factory()->create();
    $fullStackDeveloper = JobPost::factory()->create();

    // Create skills attribute
    $attribute = Attribute::factory()->create([
        'name' => 'skills',
        'type' => AttributeType::SELECT,
        'options' => json_encode(['php', 'javascript', 'java', 'python', 'html', 'css']),
    ]);

    // Set different skills
    JobAttributeValue::factory()->create([
        'job_post_id' => $phpDeveloper,
        'attribute_id' => $attribute,
        'value' => json_encode(['php', 'html', 'css']),
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $javaDeveloper,
        'attribute_id' => $attribute,
        'value' => json_encode(['java', 'javascript']),
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $fullStackDeveloper,
        'attribute_id' => $attribute,
        'value' => json_encode(['php', 'javascript', 'html', 'css']),
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with "any" mode (default)
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'skills',
        'value' => [
            'values' => ['php', 'java'],
            'mode' => 'any',
        ],
    ]);

    // Assert results - should match PHP developer, Java developer, and Full Stack developer
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(3);

    // Apply more specific filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'skills',
        'value' => [
            'values' => ['java'],
            'mode' => 'any',
        ],
    ]);

    // Assert results - should only match Java developer
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($javaDeveloper->id);
});

test('EavFilter can filter by select attribute with all mode', function () {
    // Create test data with different skills
    $phpDeveloper = JobPost::factory()->create();
    $javaDeveloper = JobPost::factory()->create();
    $fullStackDeveloper = JobPost::factory()->create();

    // Create skills attribute
    $attribute = Attribute::factory()->create([
        'name' => 'skills',
        'type' => AttributeType::SELECT,
        'options' => json_encode(['php', 'javascript', 'java', 'python', 'html', 'css']),
    ]);

    // Set different skills
    JobAttributeValue::factory()->create([
        'job_post_id' => $phpDeveloper,
        'attribute_id' => $attribute,
        'value' => json_encode(['php', 'html', 'css']),
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $javaDeveloper,
        'attribute_id' => $attribute,
        'value' => json_encode(['java', 'javascript']),
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $fullStackDeveloper,
        'attribute_id' => $attribute,
        'value' => json_encode(['php', 'javascript', 'html', 'css']),
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with "all" mode
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'skills',
        'value' => [
            'values' => ['php', 'html'],
            'mode' => 'all',
        ],
    ]);

    // Assert results - should match PHP developer and Full Stack developer (both have php AND html)
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())->toContain($phpDeveloper->id, $fullStackDeveloper->id);

    // Apply more specific filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'skills',
        'value' => [
            'values' => ['php', 'javascript', 'html'],
            'mode' => 'all',
        ],
    ]);

    // Assert results - should only match Full Stack developer
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($fullStackDeveloper->id);
});

test('EavFilter can filter by select attribute with none mode', function () {
    // Create test data with different skills
    $phpDeveloper = JobPost::factory()->create();
    $javaDeveloper = JobPost::factory()->create();
    $fullStackDeveloper = JobPost::factory()->create();

    // Create skills attribute
    $attribute = Attribute::factory()->create([
        'name' => 'skills',
        'type' => AttributeType::SELECT,
        'options' => json_encode(['php', 'javascript', 'java', 'python', 'html', 'css']),
    ]);

    // Set different skills
    JobAttributeValue::factory()->create([
        'job_post_id' => $phpDeveloper,
        'attribute_id' => $attribute,
        'value' => json_encode(['php', 'html', 'css']),
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $javaDeveloper,
        'attribute_id' => $attribute,
        'value' => json_encode(['java', 'javascript']),
    ]);

    JobAttributeValue::factory()->create([
        'job_post_id' => $fullStackDeveloper,
        'attribute_id' => $attribute,
        'value' => json_encode(['php', 'javascript', 'html', 'css']),
    ]);

    // Create filter instance
    $filter = new EavFilter;

    // Apply filter with "none" mode
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'skills',
        'value' => [
            'values' => ['java', 'python'],
            'mode' => 'none',
        ],
    ]);

    // Assert results - should match PHP developer and Full Stack developer (neither has python, but FS doesn't have java)
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($phpDeveloper->id);

    // Apply different filter
    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'name' => 'skills',
        'value' => [
            'values' => ['javascript'],
            'mode' => 'none',
        ],
    ]);

    // Assert results - should only match PHP developer
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($phpDeveloper->id);
});

test('EavFilter handles numeric string comparisons correctly', function () {
    // Create an attribute for experience
    $attribute = Attribute::factory()->create([
        'name' => 'experience',
        'type' => AttributeType::NUMBER,
    ]);

    // Create jobs with different experience values stored as strings
    $job1 = JobPost::factory()->create(['title' => 'Job 1']);
    JobAttributeValue::factory()->create([
        'job_post_id' => $job1->id,
        'attribute_id' => $attribute->id,
        'value' => '2', // String value of 2
    ]);

    $job2 = JobPost::factory()->create(['title' => 'Job 2']);
    JobAttributeValue::factory()->create([
        'job_post_id' => $job2->id,
        'attribute_id' => $attribute->id,
        'value' => '3', // String value of 3
    ]);

    $job3 = JobPost::factory()->create(['title' => 'Job 3']);
    JobAttributeValue::factory()->create([
        'job_post_id' => $job3->id,
        'attribute_id' => $attribute->id,
        'value' => '5', // String value of 5
    ]);

    $filter = new EavFilter;

    // Test with integer value 3
    $query = JobPost::query();
    $result = $filter->apply($query, [
        'name' => 'experience',
        'operator' => '>=',
        'value' => 3,
    ])->get();

    // Should only include jobs with experience >= 3
    expect($result)->toHaveCount(2)
        ->and($result->pluck('id')->toArray())
        ->toContain($job2->id)
        ->toContain($job3->id)
        ->not->toContain($job1->id);

    // Test with string value '3'
    $query = JobPost::query();
    $stringResult = $filter->apply($query, [
        'name' => 'experience',
        'operator' => '>=',
        'value' => '3',
    ])->get();

    // Should have same behavior with string value
    expect($stringResult)->toHaveCount(2)
        ->and($stringResult->pluck('id')->toArray())
        ->toContain($job2->id)
        ->toContain($job3->id)
        ->not->toContain($job1->id);
});
