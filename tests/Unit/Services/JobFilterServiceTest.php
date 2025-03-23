<?php

use App\Enums\JobType;
use App\Enums\Status;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\JobAttributeValue;
use App\Models\JobPost;
use App\Models\Language;
use App\Models\Location;
use App\Services\JobFilterService;

test('JobFilterService can handle basic field filtering', function () {
    $service = new JobFilterService;

    // Create test data
    $job = JobPost::factory()->create([
        'title' => 'Senior PHP Developer',
        'company_name' => 'Tech Corp',
        'salary_min' => 80000,
        'salary_max' => 120000,
        'is_remote' => true,
        'job_type' => JobType::FULL_TIME,
        'status' => Status::PUBLISHED,
        'published_at' => now(),
    ]);

    // Test text field filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'title LIKE "Senior" AND company_name=Tech Corp');
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);

    // Test numeric field filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'salary_min>=70000 AND salary_max<=130000');
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);

    // Test boolean field filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'is_remote=true');
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);

    // Test enum field filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'job_type=full-time AND status=published');
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);
});

test('JobFilterService can handle date field filtering', function () {
    $service = new JobFilterService;

    // Create test data
    $job = JobPost::factory()->create([
        'published_at' => now(),
    ]);

    // Test date field filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'published_at>='.now()->subDay()->toDateString());
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);

    // Test date range filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'published_at>='.now()->subWeek()->toDateString().' AND published_at<='.now()->addDay()->toDateString());
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);
});

test('JobFilterService can handle relationship filtering', function () {
    $service = new JobFilterService;

    // Create test data
    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);
    $backend = Category::factory()->create(['name' => 'Backend']);
    $frontend = Category::factory()->create(['name' => 'Frontend']);
    $newYork = Location::factory()->create(['city' => 'New York', 'state' => 'NY']);
    $sanFrancisco = Location::factory()->create(['city' => 'San Francisco', 'state' => 'CA']);

    $job = JobPost::factory()->create();
    $job->languages()->attach([$php->id, $javascript->id]);
    $job->categories()->attach($backend->id);
    $job->locations()->attach($newYork->id);

    // Test language filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'languages HAS_ANY (PHP,JavaScript)');
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);

    // Test category filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'categories HAS_ANY (Backend)');
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);

    // Test location filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'locations HAS_ANY (New York)');
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);

    // Test complex relationship filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, '(languages HAS_ANY (PHP) AND categories HAS_ANY (Backend)) OR (locations HAS_ANY (San Francisco))');
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);
});

test('JobFilterService can handle EAV attribute filtering', function () {
    $service = new JobFilterService;

    // Create test data
    $job = JobPost::factory()->create();

    // Create text attribute
    $textAttr = Attribute::factory()->create([
        'name' => 'description',
        'type' => 'text',
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $job->id,
        'attribute_id' => $textAttr->id,
        'value' => 'Senior Developer Position',
    ]);

    // Create number attribute
    $numberAttr = Attribute::factory()->create([
        'name' => 'years_experience',
        'type' => 'number',
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $job->id,
        'attribute_id' => $numberAttr->id,
        'value' => '5',
    ]);

    // Create boolean attribute
    $booleanAttr = Attribute::factory()->create([
        'name' => 'requires_degree',
        'type' => 'boolean',
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $job->id,
        'attribute_id' => $booleanAttr->id,
        'value' => 'true',
    ]);

    // Create select attribute
    $selectAttr = Attribute::factory()->create([
        'name' => 'education_level',
        'type' => 'select',
        'options' => json_encode(['bachelor', 'master', 'phd']),
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $job->id,
        'attribute_id' => $selectAttr->id,
        'value' => 'master',
    ]);

    // Test text attribute filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'attribute:description LIKE "Senior"');
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);

    // Test number attribute filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'attribute:years_experience>=3');
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);

    // Test boolean attribute filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'attribute:requires_degree=true');
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);

    // Test select attribute filtering
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'attribute:education_level=master');
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);
});

test('JobFilterService can handle complex nested conditions', function () {
    $service = new JobFilterService;

    // Create test data
    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);
    $backend = Category::factory()->create(['name' => 'Backend']);
    $frontend = Category::factory()->create(['name' => 'Frontend']);
    $newYork = Location::factory()->create(['city' => 'New York', 'state' => 'NY']);
    $sanFrancisco = Location::factory()->create(['city' => 'San Francisco', 'state' => 'CA']);

    $job = JobPost::factory()->create([
        'job_type' => JobType::FULL_TIME,
        'is_remote' => true,
        'salary_min' => 100000,
        'salary_max' => 150000,
    ]);

    $job->languages()->attach([$php->id, $javascript->id]);
    $job->categories()->attach($backend->id);
    $job->locations()->attach($newYork->id);

    // Create EAV attributes
    $yearsAttr = Attribute::factory()->create([
        'name' => 'years_experience',
        'type' => 'number',
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $job->id,
        'attribute_id' => $yearsAttr->id,
        'value' => '5',
    ]);

    // Test complex nested conditions
    $query = JobPost::query();
    $filterString = sprintf(
        '(job_type=full-time AND is_remote=true AND salary_min>=80000) AND '.
        '((languages HAS_ANY (PHP,JavaScript) AND categories HAS_ANY (Backend)) OR '.
        '(locations HAS_ANY (New York,San Francisco))) AND '.
        'attribute:years_experience>=3'
    );

    $filteredQuery = $service->apply($query, $filterString);
    expect($filteredQuery->get())->toHaveCount(1)
        ->and($filteredQuery->first()->id)->toBe($job->id);
});

test('JobFilterService handles empty and invalid filter strings gracefully', function () {
    $service = new JobFilterService;

    // Create test data
    $job1 = JobPost::factory()->create();
    $job2 = JobPost::factory()->create();

    // Test empty filter string
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, '');
    expect($filteredQuery->get())->toHaveCount(2)
        ->and($filteredQuery->pluck('id')->toArray())
        ->toContain($job1->id)
        ->toContain($job2->id);

    // Test invalid filter syntax
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'invalid syntax');
    expect($filteredQuery->get())->toHaveCount(2)
        ->and($filteredQuery->pluck('id')->toArray())
        ->toContain($job1->id)
        ->toContain($job2->id);

    // Test invalid field name
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'invalid_field=value');
    expect($filteredQuery->get())->toHaveCount(2)
        ->and($filteredQuery->pluck('id')->toArray())
        ->toContain($job1->id)
        ->toContain($job2->id);

    // Test invalid operator
    $query = JobPost::query();
    $filteredQuery = $service->apply($query, 'title INVALID_OPERATOR value');
    expect($filteredQuery->get())->toHaveCount(2)
        ->and($filteredQuery->pluck('id')->toArray())
        ->toContain($job1->id)
        ->toContain($job2->id);
});
