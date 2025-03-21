<?php

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\JobAttributeValue;
use App\Models\JobPost;

it('has correct attributes for text type', function () {
    $attribute = Attribute::factory()->create([
        'name' => 'Test Attribute',
        'type' => AttributeType::TEXT,
        'options' => null,
    ]);

    expect($attribute)
        ->id->toBeInt()
        ->name->toBe('Test Attribute')
        ->type->toBe(AttributeType::TEXT)
        ->options->toBeNull()
        ->created_at->not->toBeNull()
        ->updated_at->not->toBeNull();
});

it('has correct attributes for select type with options', function () {
    $options = ['Option 1', 'Option 2', 'Option 3'];
    $attribute = Attribute::factory()->create([
        'name' => 'Test Attribute',
        'type' => AttributeType::SELECT,
        'options' => $options,
    ]);

    expect($attribute)
        ->id->toBeInt()
        ->name->toBe('Test Attribute')
        ->type->toBe(AttributeType::SELECT)
        ->options->toBe($options)
        ->created_at->not->toBeNull()
        ->updated_at->not->toBeNull();
});

it('can have job attribute values', function () {
    $attribute = Attribute::factory()->create();
    $jobPost = JobPost::factory()->create();
    $jobAttributeValue = JobAttributeValue::factory()->create([
        'attribute_id' => $attribute,
        'job_post_id' => $jobPost,
    ]);

    expect($attribute->jobAttributeValues)
        ->toHaveCount(1)
        ->first()->id->toBe($jobAttributeValue->id);
});
