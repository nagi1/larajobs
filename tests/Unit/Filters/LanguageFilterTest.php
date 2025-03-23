<?php

use App\Filters\LanguageFilter;
use App\Models\JobPost;
use App\Models\Language;

test('language filter can filter by single language', function () {
    $filter = new LanguageFilter;

    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);

    $phpJob = JobPost::factory()->create();
    $phpJob->languages()->attach($php);

    $jsJob = JobPost::factory()->create();
    $jsJob->languages()->attach($javascript);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'PHP');

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($phpJob->id);
});

test('language filter can filter by multiple languages', function () {
    $filter = new LanguageFilter;

    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);
    $python = Language::factory()->create(['name' => 'Python']);

    $phpJob = JobPost::factory()->create();
    $phpJob->languages()->attach($php);

    $jsJob = JobPost::factory()->create();
    $jsJob->languages()->attach($javascript);

    $pythonJob = JobPost::factory()->create();
    $pythonJob->languages()->attach($python);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, ['PHP', 'JavaScript']);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($phpJob->id)
        ->toContain($jsJob->id);
});

test('language filter can filter by HAS_ANY operation', function () {
    $filter = new LanguageFilter;

    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);
    $python = Language::factory()->create(['name' => 'Python']);
    $ruby = Language::factory()->create(['name' => 'Ruby']);

    $phpJob = JobPost::factory()->create();
    $phpJob->languages()->attach($php);

    $fullStackJob = JobPost::factory()->create();
    $fullStackJob->languages()->attach([$php->id, $javascript->id]);

    $otherJob = JobPost::factory()->create();
    $otherJob->languages()->attach([$python->id, $ruby->id]);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'mode' => 'has_any',
        'values' => ['PHP', 'JavaScript'],
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($phpJob->id)
        ->toContain($fullStackJob->id)
        ->not->toContain($otherJob->id);
});

test('language filter can filter by IS_ANY operation', function () {
    $filter = new LanguageFilter;

    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);
    $python = Language::factory()->create(['name' => 'Python']);

    $phpJob = JobPost::factory()->create();
    $phpJob->languages()->attach($php);

    $jsJob = JobPost::factory()->create();
    $jsJob->languages()->attach($javascript);

    $fullStackJob = JobPost::factory()->create();
    $fullStackJob->languages()->attach([$php->id, $javascript->id]);

    $phpPythonJob = JobPost::factory()->create();
    $phpPythonJob->languages()->attach([$php->id, $python->id]);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'mode' => 'is_any',
        'values' => ['PHP', 'JavaScript'],
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($phpJob->id)
        ->toContain($jsJob->id)
        ->not->toContain($fullStackJob->id)
        ->not->toContain($phpPythonJob->id);
});

test('language filter can filter by exists operation', function () {
    $filter = new LanguageFilter;

    $php = Language::factory()->create(['name' => 'PHP']);

    $jobWithLanguage = JobPost::factory()->create();
    $jobWithLanguage->languages()->attach($php);

    $jobWithoutLanguage = JobPost::factory()->create();

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'mode' => 'exists',
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($jobWithLanguage->id);
});

test('language filter can filter by exact match', function () {
    $filter = new LanguageFilter;

    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);
    $python = Language::factory()->create(['name' => 'Python']);

    $fullStackJob = JobPost::factory()->create();
    $fullStackJob->languages()->attach([$php->id, $javascript->id]);

    $phpJob = JobPost::factory()->create();
    $phpJob->languages()->attach($php);

    $extraJob = JobPost::factory()->create();
    $extraJob->languages()->attach([$php->id, $javascript->id, $python->id]);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, [
        'mode' => '=',
        'values' => ['PHP', 'JavaScript'],
    ]);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($fullStackJob->id);
});

test('language filter ignores invalid languages', function () {
    $filter = new LanguageFilter;

    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);

    $phpJob = JobPost::factory()->create();
    $phpJob->languages()->attach($php);

    $jsJob = JobPost::factory()->create();
    $jsJob->languages()->attach($javascript);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'InvalidLanguage');

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($phpJob->id)
        ->toContain($jsJob->id);
});

test('language filter handles empty value', function () {
    $filter = new LanguageFilter;

    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);

    $phpJob = JobPost::factory()->create();
    $phpJob->languages()->attach($php);

    $jsJob = JobPost::factory()->create();
    $jsJob->languages()->attach($javascript);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, '');

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($phpJob->id)
        ->toContain($jsJob->id);
});

test('language filter handles case sensitivity correctly', function () {
    $filter = new LanguageFilter;

    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);

    $phpJob = JobPost::factory()->create();
    $phpJob->languages()->attach($php);

    $jsJob = JobPost::factory()->create();
    $jsJob->languages()->attach($javascript);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, ['php', 'JAVASCRIPT']);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($phpJob->id)
        ->toContain($jsJob->id);
});

test('language filter handles special characters in language names', function () {
    $filter = new LanguageFilter;

    $cSharp = Language::factory()->create(['name' => 'C#']);
    $cPlusPlus = Language::factory()->create(['name' => 'C++']);
    $dotNet = Language::factory()->create(['name' => '.NET']);

    $cSharpJob = JobPost::factory()->create();
    $cSharpJob->languages()->attach($cSharp);

    $cPlusPlusJob = JobPost::factory()->create();
    $cPlusPlusJob->languages()->attach($cPlusPlus);

    $dotNetJob = JobPost::factory()->create();
    $dotNetJob->languages()->attach($dotNet);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, ['C#', 'C++', '.NET']);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(3)
        ->and($results->pluck('id')->toArray())
        ->toContain($cSharpJob->id)
        ->toContain($cPlusPlusJob->id)
        ->toContain($dotNetJob->id);
});

test('language filter handles duplicate language names gracefully', function () {
    $filter = new LanguageFilter;

    // Create first PHP language
    $php = Language::factory()->create(['name' => 'PHP']);

    // Try to create second PHP language with different case
    // This should fail due to unique constraint, which is what we want
    try {
        Language::factory()->create(['name' => 'php']);
    } catch (\Illuminate\Database\QueryException $e) {
        // Expected behavior - unique constraint violation
    }

    $job1 = JobPost::factory()->create();
    $job1->languages()->attach($php);

    $job2 = JobPost::factory()->create();
    $job2->languages()->attach($php);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, 'PHP');

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(2)
        ->and($results->pluck('id')->toArray())
        ->toContain($job1->id)
        ->toContain($job2->id);
});

test('language filter handles very long language names', function () {
    $filter = new LanguageFilter;

    $longName = str_repeat('A', 255);
    $longLanguage = Language::factory()->create(['name' => $longName]);

    $job = JobPost::factory()->create();
    $job->languages()->attach($longLanguage);

    $query = JobPost::query();
    $filteredQuery = $filter->apply($query, $longName);

    $results = $filteredQuery->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($job->id);
});

test('language filter handles different empty value types consistently', function () {
    $filter = new LanguageFilter;

    $php = Language::factory()->create(['name' => 'PHP']);
    $javascript = Language::factory()->create(['name' => 'JavaScript']);

    $phpJob = JobPost::factory()->create();
    $phpJob->languages()->attach($php);

    $jsJob = JobPost::factory()->create();
    $jsJob->languages()->attach($javascript);

    $query = JobPost::query();
    $results1 = $filter->apply($query, null)->get();
    $results2 = $filter->apply($query, '')->get();
    $results3 = $filter->apply($query, [])->get();

    expect($results1)->toHaveCount(2)
        ->and($results2)->toHaveCount(2)
        ->and($results3)->toHaveCount(2)
        ->and($results1->pluck('id')->toArray())
        ->toEqual($results2->pluck('id')->toArray())
        ->toEqual($results3->pluck('id')->toArray())
        ->toContain($phpJob->id)
        ->toContain($jsJob->id);
});

test('language filter handles whitespace in language names', function () {
    $filter = new LanguageFilter;

    $language = Language::factory()->create(['name' => '  PHP  ']);
    $job = JobPost::factory()->create();
    $job->languages()->attach($language);

    $query = JobPost::query();
    $results1 = $filter->apply($query, 'PHP')->get();
    $results2 = $filter->apply($query, '  PHP  ')->get();
    $results3 = $filter->apply($query, 'php  ')->get();

    expect($results1)->toHaveCount(1)
        ->and($results2)->toHaveCount(1)
        ->and($results3)->toHaveCount(1)
        ->and($results1->first()->id)->toBe($job->id)
        ->and($results2->first()->id)->toBe($job->id)
        ->and($results3->first()->id)->toBe($job->id);
});
