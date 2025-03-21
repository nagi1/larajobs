<?php

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\JobAttributeValue;
use App\Models\JobPost;
use App\Models\Language;
use App\Models\Location;

test('job post has required attributes', function () {
    $jobPost = JobPost::factory()->create([
        'title' => 'Senior Developer',
        'company_name' => 'Test Company',
        'salary_min' => 80000,
        'salary_max' => 120000,
        'is_remote' => true,
        'job_type' => JobType::FULL_TIME,
        'status' => JobStatus::PUBLISHED,
    ]);

    expect($jobPost->title)->toBe('Senior Developer')
        ->and($jobPost->company_name)->toBe('Test Company')
        ->and((float) $jobPost->salary_min)->toBe(80000.00)
        ->and((float) $jobPost->salary_max)->toBe(120000.00)
        ->and($jobPost->is_remote)->toBeTrue()
        ->and($jobPost->job_type)->toBe(JobType::FULL_TIME)
        ->and($jobPost->status)->toBe(JobStatus::PUBLISHED);
});

test('job post has correct attributes structure', function () {
    $jobPost = JobPost::factory()->create()->refresh();

    expect(array_keys($jobPost->toArray()))->toBe([
        'id',
        'title',
        'description',
        'company_name',
        'salary_min',
        'salary_max',
        'is_remote',
        'job_type',
        'status',
        'published_at',
        'created_at',
        'updated_at',
    ]);
});

test('job post can have languages', function () {
    $jobPost = JobPost::factory()->create();
    $language = Language::factory()->create();

    $jobPost->languages()->attach($language);

    expect($jobPost->languages)->toHaveCount(1)
        ->and($jobPost->languages->contains($language))->toBeTrue();
});

test('job post can have locations', function () {
    $jobPost = JobPost::factory()->create();
    $location = Location::factory()->create();

    $jobPost->locations()->attach($location);

    expect($jobPost->locations)->toHaveCount(1)
        ->and($jobPost->locations->contains($location))->toBeTrue();
});

test('job post can have categories', function () {
    $jobPost = JobPost::factory()->create();
    $category = Category::factory()->create();

    $jobPost->categories()->attach($category);

    expect($jobPost->categories)->toHaveCount(1)
        ->and($jobPost->categories->contains($category))->toBeTrue();
});

test('job post can have attribute values', function () {
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create();
    $jobAttributeValue = JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
    ]);

    expect($jobPost->jobAttributeValues)->toHaveCount(1)
        ->and($jobPost->jobAttributeValues->contains($jobAttributeValue))->toBeTrue();
});
