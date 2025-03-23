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
