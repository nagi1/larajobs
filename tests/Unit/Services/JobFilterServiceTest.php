<?php

use App\Enums\JobType;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\JobAttributeValue;
use App\Models\JobPost;
use App\Models\Language;
use App\Services\JobFilterService;

test('JobFilterService can handle basic AND conditions', function () {
    $service = new JobFilterService;

    // Create test data
    $fullTimeJob = JobPost::factory()->create([
        'job_type' => JobType::FULL_TIME,
        'is_remote' => true,
    ]);
    $partTimeJob = JobPost::factory()->create([
        'job_type' => JobType::PART_TIME,
        'is_remote' => false,
    ]);

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'job_type=full-time AND is_remote=true');

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($fullTimeJob->id);
});

test('JobFilterService can handle OR conditions', function () {
    $service = new JobFilterService;

    // Create test data
    $fullTimeJob = JobPost::factory()->create([
        'job_type' => JobType::FULL_TIME,
        'is_remote' => false,
    ]);
    $partTimeJob = JobPost::factory()->create([
        'job_type' => JobType::PART_TIME,
        'is_remote' => true,
    ]);

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'job_type=full-time OR is_remote=true');

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($fullTimeJob->id)
        ->toContain($partTimeJob->id);
});

test('JobFilterService can handle grouped conditions', function () {
    $service = new JobFilterService;

    // Create test data
    $fullTimeRemoteJob = JobPost::factory()->create([
        'job_type' => JobType::FULL_TIME,
        'is_remote' => true,
    ]);
    $partTimeOfficeJob = JobPost::factory()->create([
        'job_type' => JobType::PART_TIME,
        'is_remote' => false,
    ]);

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, '(job_type=full-time AND is_remote=true) OR (job_type=part-time AND is_remote=false)');

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($fullTimeRemoteJob->id)
        ->toContain($partTimeOfficeJob->id);
});

test('JobFilterService can handle EAV attributes', function () {
    $service = new JobFilterService;

    // Create test data
    $job = JobPost::factory()->create();
    $attribute = Attribute::factory()->create([
        'name' => 'years_experience',
        'type' => 'number',
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $job,
        'attribute_id' => $attribute,
        'value' => '5',
    ]);

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'attribute:years_experience>=3');

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($job->id);
});

test('JobFilterService can handle relationship filters', function () {
    $service = new JobFilterService;

    // Create test data
    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);

    $phpJob = JobPost::factory()->create();
    $phpJob->languages()->attach($php);

    $jsJob = JobPost::factory()->create();
    $jsJob->languages()->attach($javascript);

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'languages HAS_ANY (PHP,JavaScript)');

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($phpJob->id)
        ->toContain($jsJob->id);
});

test('JobFilterService can handle complex nested conditions', function () {
    $service = new JobFilterService;

    // Create test data
    $backend = Category::factory()->create(['name' => 'Backend']);
    $frontend = Category::factory()->create(['name' => 'Frontend']);

    $fullTimeBackendJob = JobPost::factory()->create([
        'job_type' => JobType::FULL_TIME,
        'is_remote' => true,
    ]);
    $fullTimeBackendJob->categories()->attach($backend);

    $partTimeFrontendJob = JobPost::factory()->create([
        'job_type' => JobType::PART_TIME,
        'is_remote' => false,
    ]);
    $partTimeFrontendJob->categories()->attach($frontend);

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, '(job_type=full-time AND categories HAS_ANY (Backend)) OR (job_type=part-time AND categories HAS_ANY (Frontend))');

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($fullTimeBackendJob->id)
        ->toContain($partTimeFrontendJob->id);
});

test('JobFilterService handles empty filter string', function () {
    $service = new JobFilterService;

    // Create test data
    $job1 = JobPost::factory()->create();
    $job2 = JobPost::factory()->create();

    // Apply filter
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, '');

    // Assert results
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($job1->id)
        ->toContain($job2->id);
});

test('JobFilterService handles invalid filter syntax gracefully', function () {
    $service = new JobFilterService;

    // Create test data
    $job1 = JobPost::factory()->create();
    $job2 = JobPost::factory()->create();

    // Apply filter with invalid syntax
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'invalid syntax');

    // Assert results (should return all jobs)
    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($job1->id)
        ->toContain($job2->id);
});
