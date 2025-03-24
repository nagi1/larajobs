<?php

use App\Enums\JobStatus;
use App\Enums\JobType;
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
        'status' => JobStatus::PUBLISHED,
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

test('JobFilterService handles the specific complex with multiple filters', function () {
    $service = new JobFilterService;

    // Create test data
    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);
    $python = Language::factory()->create(['name' => 'Python']);

    $newYork = Location::factory()->create(['city' => 'New York', 'state' => 'NY']);
    $remote = Location::factory()->create(['city' => 'Remote', 'state' => 'Remote']);
    $sanFrancisco = Location::factory()->create(['city' => 'San Francisco', 'state' => 'CA']);

    // Create a job that matches all filter conditions
    $matchingJob = JobPost::factory()->create([
        'job_type' => JobType::FULL_TIME,
    ]);
    $matchingJob->languages()->attach([$php->id, $javascript->id]);
    $matchingJob->locations()->attach([$newYork->id]);

    // Create the years_experience attribute
    $yearsExpAttr = Attribute::factory()->create([
        'name' => 'years_experience',
        'type' => 'number',
    ]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $matchingJob->id,
        'attribute_id' => $yearsExpAttr->id,
        'value' => '5', // More than 3 as required
    ]);

    // Create a job that doesn't match - part time
    $wrongTypeJob = JobPost::factory()->create([
        'job_type' => JobType::PART_TIME,
    ]);
    $wrongTypeJob->languages()->attach([$php->id, $javascript->id]);
    $wrongTypeJob->locations()->attach([$newYork->id]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $wrongTypeJob->id,
        'attribute_id' => $yearsExpAttr->id,
        'value' => '5',
    ]);

    // Create a job that doesn't match - wrong languages
    $wrongLangJob = JobPost::factory()->create([
        'job_type' => JobType::FULL_TIME,
    ]);
    $wrongLangJob->languages()->attach([$python->id]);
    $wrongLangJob->locations()->attach([$newYork->id]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $wrongLangJob->id,
        'attribute_id' => $yearsExpAttr->id,
        'value' => '5',
    ]);

    // Create a job that doesn't match - wrong location
    $wrongLocJob = JobPost::factory()->create([
        'job_type' => JobType::FULL_TIME,
    ]);
    $wrongLocJob->languages()->attach([$php->id, $javascript->id]);
    $wrongLocJob->locations()->attach([$sanFrancisco->id]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $wrongLocJob->id,
        'attribute_id' => $yearsExpAttr->id,
        'value' => '5',
    ]);

    // Create a job that doesn't match - not enough experience
    $lowExpJob = JobPost::factory()->create([
        'job_type' => JobType::FULL_TIME,
    ]);
    $lowExpJob->languages()->attach([$php->id, $javascript->id]);
    $lowExpJob->locations()->attach([$newYork->id]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $lowExpJob->id,
        'attribute_id' => $yearsExpAttr->id,
        'value' => '2', // Less than 3 as required
    ]);

    // Use the exact filter string from Challenge.md
    $query = JobPost::query();
    $filterString = '(job_type=full-time AND (languages HAS_ANY (PHP,JavaScript))) AND (locations HAS_ANY (New York,Remote)) AND attribute:years_experience>=3';

    // Test each part of the filter individually
    echo "\n----------- Testing job_type=full-time -----------\n";
    $query1 = JobPost::query();
    $filteredQuery1 = $service->apply($query1, 'job_type=full-time');
    $results1 = $filteredQuery1->with(['languages', 'locations'])->get();
    echo 'Jobs matching job_type=full-time: '.$results1->count()."\n";

    echo "\n----------- Testing languages HAS_ANY (PHP,JavaScript) -----------\n";
    $query2 = JobPost::query();
    $filteredQuery2 = $service->apply($query2, 'languages HAS_ANY (PHP,JavaScript)');
    $results2 = $filteredQuery2->with(['languages', 'locations'])->get();
    echo 'Jobs matching languages HAS_ANY (PHP,JavaScript): '.$results2->count()."\n";

    echo "\n----------- Testing locations HAS_ANY (New York,Remote) -----------\n";
    $query3 = JobPost::query();
    $filteredQuery3 = $service->apply($query3, 'locations HAS_ANY (New York,Remote)');
    $results3 = $filteredQuery3->with(['languages', 'locations'])->get();
    echo 'Jobs matching locations HAS_ANY (New York,Remote): '.$results3->count()."\n";

    echo "\n----------- Testing attribute:years_experience>=3 -----------\n";
    $query4 = JobPost::query();
    $filteredQuery4 = $service->apply($query4, 'attribute:years_experience>=3');
    $results4 = $filteredQuery4->with(['languages', 'locations'])->get();
    echo 'Jobs matching attribute:years_experience>=3: '.$results4->count()."\n";

    echo "\n----------- Testing job_type=full-time AND languages HAS_ANY (PHP,JavaScript) -----------\n";
    $query5 = JobPost::query();
    $filteredQuery5 = $service->apply($query5, 'job_type=full-time AND languages HAS_ANY (PHP,JavaScript)');
    $results5 = $filteredQuery5->with(['languages', 'locations'])->get();
    echo 'Jobs matching job_type=full-time AND languages HAS_ANY (PHP,JavaScript): '.$results5->count()."\n";

    echo "\n----------- Testing Full Complex Query -----------\n";
    $filteredQuery = $service->apply($query, $filterString);
    $results = $filteredQuery->with(['languages', 'locations'])->get();

    // Debug which jobs are being returned
    foreach ($results as $result) {
        echo "Job ID: {$result->id}, Job Type: {$result->job_type->value}\n";
        echo 'Languages: '.implode(', ', $result->languages->pluck('name')->toArray())."\n";
        echo 'Locations: '.implode(', ', $result->locations->pluck('city')->toArray())."\n";

        // Get the years_experience attribute
        $expAttribute = JobAttributeValue::where('job_post_id', $result->id)
            ->whereHas('attribute', function ($query) {
                $query->where('name', 'years_experience');
            })
            ->first();

        echo 'Years Experience: '.($expAttribute ? $expAttribute->value : 'N/A')."\n\n";
    }

    // Restore the assertion
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($matchingJob->id);

    // Let's also test with a Remote location job
    $remoteJob = JobPost::factory()->create([
        'job_type' => JobType::FULL_TIME,
    ]);
    $remoteJob->languages()->attach([$php->id]);
    $remoteJob->locations()->attach([$remote->id]);
    JobAttributeValue::factory()->create([
        'job_post_id' => $remoteJob->id,
        'attribute_id' => $yearsExpAttr->id,
        'value' => '4',
    ]);

    // Run the query again - should now match 2 jobs
    $filteredQuery = $service->apply($query, $filterString);
    expect($filteredQuery->get())->toHaveCount(2)
        ->and($filteredQuery->pluck('id')->toArray())
        ->toContain($matchingJob->id)
        ->toContain($remoteJob->id);
});
