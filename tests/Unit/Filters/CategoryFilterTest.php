<?php

use App\Filters\CategoryFilter;
use App\Models\Category;
use App\Models\JobPost;

test('category filter can filter by single category', function () {
    $filter = new CategoryFilter;

    $backend = Category::factory()->create(['name' => 'Backend']);
    $frontend = Category::factory()->create(['name' => 'Frontend']);

    $backendJob = JobPost::factory()->create();
    $backendJob->categories()->attach($backend);

    $frontendJob = JobPost::factory()->create();
    $frontendJob->categories()->attach($frontend);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'Backend');

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($backendJob->id);
});

test('category filter can filter by multiple categories', function () {
    $filter = new CategoryFilter;

    $backend = Category::factory()->create(['name' => 'Backend']);
    $frontend = Category::factory()->create(['name' => 'Frontend']);
    $devops = Category::factory()->create(['name' => 'DevOps']);

    $backendJob = JobPost::factory()->create();
    $backendJob->categories()->attach($backend);

    $frontendJob = JobPost::factory()->create();
    $frontendJob->categories()->attach($frontend);

    $devopsJob = JobPost::factory()->create();
    $devopsJob->categories()->attach($devops);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, ['Backend', 'Frontend']);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($backendJob->id)
        ->toContain($frontendJob->id);
});

test('category filter can filter by HAS_ANY operation', function () {
    $filter = new CategoryFilter;

    $backend = Category::factory()->create(['name' => 'Backend']);
    $frontend = Category::factory()->create(['name' => 'Frontend']);
    $devops = Category::factory()->create(['name' => 'DevOps']);
    $mobile = Category::factory()->create(['name' => 'Mobile']);

    $backendJob = JobPost::factory()->create();
    $backendJob->categories()->attach($backend);

    $fullStackJob = JobPost::factory()->create();
    $fullStackJob->categories()->attach([$backend->id, $frontend->id]);

    $otherJob = JobPost::factory()->create();
    $otherJob->categories()->attach([$devops->id, $mobile->id]);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'mode' => 'has_any',
        'values' => ['Backend', 'Frontend'],
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($backendJob->id)
        ->toContain($fullStackJob->id)
        ->not->toContain($otherJob->id);
});

test('category filter can filter by IS_ANY operation', function () {
    $filter = new CategoryFilter;

    $backend = Category::factory()->create(['name' => 'Backend']);
    $frontend = Category::factory()->create(['name' => 'Frontend']);
    $devops = Category::factory()->create(['name' => 'DevOps']);

    $backendJob = JobPost::factory()->create();
    $backendJob->categories()->attach($backend);

    $frontendJob = JobPost::factory()->create();
    $frontendJob->categories()->attach($frontend);

    $fullStackJob = JobPost::factory()->create();
    $fullStackJob->categories()->attach([$backend->id, $frontend->id]);

    $backendDevopsJob = JobPost::factory()->create();
    $backendDevopsJob->categories()->attach([$backend->id, $devops->id]);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'mode' => 'is_any',
        'values' => ['Backend', 'Frontend'],
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($backendJob->id)
        ->toContain($frontendJob->id)
        ->not->toContain($fullStackJob->id)
        ->not->toContain($backendDevopsJob->id);
});

test('category filter can filter by exists operation', function () {
    $filter = new CategoryFilter;

    $backend = Category::factory()->create(['name' => 'Backend']);

    $jobWithCategory = JobPost::factory()->create();
    $jobWithCategory->categories()->attach($backend);

    $jobWithoutCategory = JobPost::factory()->create();

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'mode' => 'exists',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobWithCategory->id);
});

test('category filter can filter by exact match', function () {
    $filter = new CategoryFilter;

    $backend = Category::factory()->create(['name' => 'Backend']);
    $frontend = Category::factory()->create(['name' => 'Frontend']);
    $devops = Category::factory()->create(['name' => 'DevOps']);

    $fullStackJob = JobPost::factory()->create();
    $fullStackJob->categories()->attach([$backend->id, $frontend->id]);

    $backendJob = JobPost::factory()->create();
    $backendJob->categories()->attach($backend);

    $extraJob = JobPost::factory()->create();
    $extraJob->categories()->attach([$backend->id, $frontend->id, $devops->id]);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'mode' => '=',
        'values' => ['Backend', 'Frontend'],
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($fullStackJob->id);
});

test('category filter ignores invalid categories', function () {
    $filter = new CategoryFilter;

    $backend = Category::factory()->create(['name' => 'Backend']);
    $frontend = Category::factory()->create(['name' => 'Frontend']);

    $backendJob = JobPost::factory()->create();
    $backendJob->categories()->attach($backend);

    $frontendJob = JobPost::factory()->create();
    $frontendJob->categories()->attach($frontend);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'InvalidCategory');

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($backendJob->id)
        ->toContain($frontendJob->id);
});

test('category filter handles empty value', function () {
    $filter = new CategoryFilter;

    $backend = Category::factory()->create(['name' => 'Backend']);
    $frontend = Category::factory()->create(['name' => 'Frontend']);

    $backendJob = JobPost::factory()->create();
    $backendJob->categories()->attach($backend);

    $frontendJob = JobPost::factory()->create();
    $frontendJob->categories()->attach($frontend);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, '');

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($backendJob->id)
        ->toContain($frontendJob->id);
});

test('category filter handles case sensitivity correctly', function () {
    $filter = new CategoryFilter;

    $backend = Category::factory()->create(['name' => 'Backend']);
    $frontend = Category::factory()->create(['name' => 'Frontend']);

    $backendJob = JobPost::factory()->create();
    $backendJob->categories()->attach($backend);

    $frontendJob = JobPost::factory()->create();
    $frontendJob->categories()->attach($frontend);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, ['backend', 'FRONTEND']);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($backendJob->id)
        ->toContain($frontendJob->id);
});

test('category filter handles special characters in category names', function () {
    $filter = new CategoryFilter;

    $cSharp = Category::factory()->create(['name' => 'C# Development']);
    $dotNet = Category::factory()->create(['name' => '.NET']);
    $aspNet = Category::factory()->create(['name' => 'ASP.NET']);

    $cSharpJob = JobPost::factory()->create();
    $cSharpJob->categories()->attach($cSharp);

    $dotNetJob = JobPost::factory()->create();
    $dotNetJob->categories()->attach($dotNet);

    $aspNetJob = JobPost::factory()->create();
    $aspNetJob->categories()->attach($aspNet);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, ['C# Development', '.NET', 'ASP.NET']);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(3)
        ->and($results->pluck('id')->toArray())
        ->toContain($cSharpJob->id)
        ->toContain($dotNetJob->id)
        ->toContain($aspNetJob->id);
});

test('category filter handles whitespace in category names', function () {
    $filter = new CategoryFilter;

    $category = Category::factory()->create(['name' => '  Backend Development  ']);
    $job = JobPost::factory()->create();
    $job->categories()->attach($category);

    $query = JobPost::query();
    $results1 = $filter->apply($query, 'Backend Development')->get();
    $results2 = $filter->apply($query, '  Backend Development  ')->get();
    $results3 = $filter->apply($query, 'backend development  ')->get();

    expect($results1)->toHaveCount(1)
        ->and($results2)->toHaveCount(1)
        ->and($results3)->toHaveCount(1)
        ->and($results1->first()->id)->toBe($job->id)
        ->and($results2->first()->id)->toBe($job->id)
        ->and($results3->first()->id)->toBe($job->id);
});
