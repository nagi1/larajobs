<?php

use App\Filters\LocationFilter;
use App\Models\JobPost;
use App\Models\Location;

test('location filter can filter by single city', function () {
    $filter = new LocationFilter;

    $nyc = Location::factory()->create([
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
    ]);

    $london = Location::factory()->create([
        'city' => 'London',
        'state' => 'England',
        'country' => 'UK',
    ]);

    $nycJob = JobPost::factory()->create();
    $nycJob->locations()->attach($nyc);

    $londonJob = JobPost::factory()->create();
    $londonJob->locations()->attach($london);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'New York');

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($nycJob->id);
});

test('location filter can filter by multiple cities', function () {
    $filter = new LocationFilter;

    $nyc = Location::factory()->create([
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
    ]);

    $london = Location::factory()->create([
        'city' => 'London',
        'state' => 'England',
        'country' => 'UK',
    ]);

    $tokyo = Location::factory()->create([
        'city' => 'Tokyo',
        'state' => 'Tokyo',
        'country' => 'Japan',
    ]);

    $nycJob = JobPost::factory()->create();
    $nycJob->locations()->attach($nyc);

    $londonJob = JobPost::factory()->create();
    $londonJob->locations()->attach($london);

    $tokyoJob = JobPost::factory()->create();
    $tokyoJob->locations()->attach($tokyo);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, ['New York', 'London']);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($nycJob->id)
        ->toContain($londonJob->id);
});

test('location filter can filter by HAS_ANY operation', function () {
    $filter = new LocationFilter;

    $nyc = Location::factory()->create([
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
    ]);

    $london = Location::factory()->create([
        'city' => 'London',
        'state' => 'England',
        'country' => 'UK',
    ]);

    $tokyo = Location::factory()->create([
        'city' => 'Tokyo',
        'state' => 'Tokyo',
        'country' => 'Japan',
    ]);

    $nycJob = JobPost::factory()->create();
    $nycJob->locations()->attach($nyc);

    $multiLocationJob = JobPost::factory()->create();
    $multiLocationJob->locations()->attach([$nyc->id, $london->id]);

    $tokyoJob = JobPost::factory()->create();
    $tokyoJob->locations()->attach($tokyo);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'mode' => 'has_any',
        'values' => ['New York', 'London'],
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($nycJob->id)
        ->toContain($multiLocationJob->id)
        ->not->toContain($tokyoJob->id);
});

test('location filter can filter by IS_ANY operation', function () {
    $filter = new LocationFilter;

    $nyc = Location::factory()->create([
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
    ]);

    $london = Location::factory()->create([
        'city' => 'London',
        'state' => 'England',
        'country' => 'UK',
    ]);

    $singleLocationJob = JobPost::factory()->create();
    $singleLocationJob->locations()->attach($nyc);

    $multiLocationJob = JobPost::factory()->create();
    $multiLocationJob->locations()->attach([$nyc->id, $london->id]);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'mode' => 'is_any',
        'values' => ['New York', 'London'],
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($singleLocationJob->id);
});

test('location filter can filter by exists operation', function () {
    $filter = new LocationFilter;

    $nyc = Location::factory()->create([
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
    ]);

    $jobWithLocation = JobPost::factory()->create();
    $jobWithLocation->locations()->attach($nyc);

    $jobWithoutLocation = JobPost::factory()->create();

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'mode' => 'exists',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobWithLocation->id);
});

test('location filter can filter by exact match', function () {
    $filter = new LocationFilter;

    $nyc = Location::factory()->create([
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
    ]);

    $london = Location::factory()->create([
        'city' => 'London',
        'state' => 'England',
        'country' => 'UK',
    ]);

    $tokyo = Location::factory()->create([
        'city' => 'Tokyo',
        'state' => 'Tokyo',
        'country' => 'Japan',
    ]);

    $exactMatchJob = JobPost::factory()->create();
    $exactMatchJob->locations()->attach([$nyc->id, $london->id]);

    $singleLocationJob = JobPost::factory()->create();
    $singleLocationJob->locations()->attach($nyc);

    $extraLocationJob = JobPost::factory()->create();
    $extraLocationJob->locations()->attach([$nyc->id, $london->id, $tokyo->id]);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'mode' => '=',
        'values' => ['New York', 'London'],
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($exactMatchJob->id);
});

test('location filter can filter by state', function () {
    $filter = new LocationFilter;

    $nyc = Location::factory()->create([
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
    ]);

    $buffalo = Location::factory()->create([
        'city' => 'Buffalo',
        'state' => 'NY',
        'country' => 'USA',
    ]);

    $london = Location::factory()->create([
        'city' => 'London',
        'state' => 'England',
        'country' => 'UK',
    ]);

    $nycJob = JobPost::factory()->create();
    $nycJob->locations()->attach($nyc);

    $buffaloJob = JobPost::factory()->create();
    $buffaloJob->locations()->attach($buffalo);

    $londonJob = JobPost::factory()->create();
    $londonJob->locations()->attach($london);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'field' => 'state',
        'values' => 'NY',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($nycJob->id)
        ->toContain($buffaloJob->id)
        ->not->toContain($londonJob->id);
});

test('location filter ignores invalid locations', function () {
    $filter = new LocationFilter;

    $nyc = Location::factory()->create([
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
    ]);

    $london = Location::factory()->create([
        'city' => 'London',
        'state' => 'England',
        'country' => 'UK',
    ]);

    $nycJob = JobPost::factory()->create();
    $nycJob->locations()->attach($nyc);

    $londonJob = JobPost::factory()->create();
    $londonJob->locations()->attach($london);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'InvalidCity');

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($nycJob->id)
        ->toContain($londonJob->id);
});

test('location filter handles empty value', function () {
    $filter = new LocationFilter;

    $nyc = Location::factory()->create([
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
    ]);

    $job = JobPost::factory()->create();
    $job->locations()->attach($nyc);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, '');

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1);
});

test('location filter handles case sensitivity correctly', function () {
    $filter = new LocationFilter;

    $nyc = Location::factory()->create([
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
    ]);

    $job = JobPost::factory()->create();
    $job->locations()->attach($nyc);

    $query = JobPost::query();
    $results1 = $filter->apply($query, 'NEW YORK')->get();
    $results2 = $filter->apply($query, 'new york')->get();

    expect($results1)->toHaveCount(1)
        ->and($results2)->toHaveCount(1)
        ->and($results1->first()->id)->toBe($job->id)
        ->and($results2->first()->id)->toBe($job->id);
});

test('location filter handles special characters in location names', function () {
    $filter = new LocationFilter;

    $location = Location::factory()->create([
        'city' => 'Saint-Étienne',
        'state' => 'Auvergne-Rhône-Alpes',
        'country' => 'France',
    ]);

    $job = JobPost::factory()->create();
    $job->locations()->attach($location);

    $query = JobPost::query();
    $results = $filter->apply($query, 'Saint-Étienne')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($job->id);
});

test('location filter handles whitespace in location names', function () {
    $filter = new LocationFilter;

    $location = Location::factory()->create([
        'city' => '  New York  ',
        'state' => 'NY',
        'country' => 'USA',
    ]);

    $job = JobPost::factory()->create();
    $job->locations()->attach($location);

    $query = JobPost::query();
    $results1 = $filter->apply($query, 'New York')->get();
    $results2 = $filter->apply($query, '  New York  ')->get();

    expect($results1)->toHaveCount(1)
        ->and($results2)->toHaveCount(1)
        ->and($results1->first()->id)->toBe($job->id)
        ->and($results2->first()->id)->toBe($job->id);
});
