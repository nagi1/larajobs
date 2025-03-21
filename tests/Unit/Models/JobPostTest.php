<?php

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Models\JobPost;

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
