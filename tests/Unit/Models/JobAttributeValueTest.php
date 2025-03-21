<?php

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\JobAttributeValue;
use App\Models\JobPost;
use Illuminate\Support\Carbon;

it('has correct attributes', function () {
    $jobPost = JobPost::factory()->create();
    $attribute = Attribute::factory()->create();
    $jobAttributeValue = JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
        'attribute_id' => $attribute,
        'value' => 'Test Value',
    ]);

    expect($jobAttributeValue)
        ->id->toBeInt()
        ->job_post_id->toBe($jobPost->id)
        ->attribute_id->toBe($attribute->id)
        ->value->toBe('Test Value')
        ->created_at->not->toBeNull()
        ->updated_at->not->toBeNull();
});

it('belongs to job post', function () {
    $jobPost = JobPost::factory()->create();
    $jobAttributeValue = JobAttributeValue::factory()->create([
        'job_post_id' => $jobPost,
    ]);

    expect($jobAttributeValue->jobPost->id)->toBe($jobPost->id);
});

it('belongs to attribute', function () {
    $attribute = Attribute::factory()->create();
    $jobAttributeValue = JobAttributeValue::factory()->create([
        'attribute_id' => $attribute,
    ]);

    expect($jobAttributeValue->attribute->id)->toBe($attribute->id);
});

it('generates correct value for text type', function () {
    $attribute = Attribute::factory()->create([
        'type' => AttributeType::TEXT,
    ]);

    $value = 'Sample text value';
    $jobAttributeValue = JobAttributeValue::factory()->create([
        'attribute_id' => $attribute,
        'value' => $value,
    ]);

    expect($jobAttributeValue->value)->toBe($value);
});

it('generates correct value for number type', function () {
    $attribute = Attribute::factory()->create([
        'type' => AttributeType::NUMBER,
    ]);

    $value = '42';
    $jobAttributeValue = JobAttributeValue::factory()->create([
        'attribute_id' => $attribute,
        'value' => $value,
    ]);

    expect($jobAttributeValue->value)->toBe($value);
});

it('generates correct value for boolean type', function () {
    $attribute = Attribute::factory()->create([
        'type' => AttributeType::BOOLEAN,
    ]);

    $value = '1';
    $jobAttributeValue = JobAttributeValue::factory()->create([
        'attribute_id' => $attribute,
        'value' => $value,
    ]);

    expect($jobAttributeValue->value)->toBe($value);
});

it('generates correct value for date type', function () {
    $testDate = Carbon::create(2024, 3, 21);
    Carbon::setTestNow($testDate);

    $attribute = Attribute::factory()->create([
        'type' => AttributeType::DATE,
    ]);

    $value = $testDate->format('Y-m-d');
    $jobAttributeValue = JobAttributeValue::factory()->create([
        'attribute_id' => $attribute,
        'value' => $value,
    ]);

    expect($jobAttributeValue->value)->toBe($value);

    Carbon::setTestNow();
});

it('generates correct value for select type', function () {
    $options = ['Option 1', 'Option 2'];
    $attribute = Attribute::factory()->create([
        'type' => AttributeType::SELECT,
        'options' => $options,
    ]);

    $value = 'Option 1';
    $jobAttributeValue = JobAttributeValue::factory()->create([
        'attribute_id' => $attribute,
        'value' => $value,
    ]);

    expect($jobAttributeValue->value)->toBe($value);
});

it('validates select type options', function () {
    $options = ['Option 1', 'Option 2'];
    $attribute = Attribute::factory()->create([
        'type' => AttributeType::SELECT,
        'options' => $options,
    ]);

    $value = 'Option 1';
    $jobAttributeValue = JobAttributeValue::factory()->create([
        'attribute_id' => $attribute,
        'value' => $value,
    ]);

    expect($jobAttributeValue->value)->toBeIn($options);
});
