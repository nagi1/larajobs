<?php

use App\Models\JobPost;
use App\Models\Location;

test('location has correct attributes structure', function () {
    $location = Location::factory()->create()->refresh();

    expect(array_keys($location->toArray()))->toBe([
        'id',
        'city',
        'state',
        'country',
        'created_at',
        'updated_at',
    ]);
});

test('location can have job posts', function () {
    $location = Location::factory()->create();
    $jobPost = JobPost::factory()->create();

    $location->jobPosts()->attach($jobPost);

    expect($location->jobPosts)->toHaveCount(1)
        ->and($location->jobPosts->contains($jobPost))->toBeTrue();
});
