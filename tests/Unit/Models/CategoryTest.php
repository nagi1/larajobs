<?php

use App\Models\Category;
use App\Models\JobPost;

test('category has correct attributes structure', function () {
    $category = Category::factory()->create()->refresh();

    expect(array_keys($category->toArray()))->toBe([
        'id',
        'name',
        'created_at',
        'updated_at',
    ]);
});

test('category can have job posts', function () {
    $category = Category::factory()->create();
    $jobPost = JobPost::factory()->create();

    $category->jobPosts()->attach($jobPost);

    expect($category->jobPosts)->toHaveCount(1)
        ->and($category->jobPosts->contains($jobPost))->toBeTrue();
});
