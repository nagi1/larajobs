<?php

use App\Models\JobPost;
use App\Models\Language;

test('language has correct attributes structure', function () {
    $language = Language::factory()->create()->refresh();

    expect(array_keys($language->toArray()))->toBe([
        'id',
        'name',
        'created_at',
        'updated_at',
    ]);
});

test('language can have job posts', function () {
    $language = Language::factory()->create();
    $jobPost = JobPost::factory()->create();

    $language->jobPosts()->attach($jobPost);

    expect($language->jobPosts)->toHaveCount(1)
        ->and($language->jobPosts->contains($jobPost))->toBeTrue();
});
