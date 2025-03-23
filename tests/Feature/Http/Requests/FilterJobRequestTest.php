<?php

use App\Http\Requests\FilterJobRequest;
use Illuminate\Support\Facades\Validator;

test('FilterJobRequest validates filter parameter', function () {
    $request = new FilterJobRequest;
    $validator = Validator::make(
        ['filter' => 'title=Software Engineer'],
        $request->rules()
    );

    expect($validator->passes())->toBeTrue();
});

test('FilterJobRequest validates sort parameter', function () {
    $request = new FilterJobRequest;
    $validator = Validator::make(
        ['sort' => 'title'],
        $request->rules()
    );

    expect($validator->passes())->toBeTrue();
});

test('FilterJobRequest validates order parameter', function () {
    $request = new FilterJobRequest;
    $validator = Validator::make(
        ['order' => 'asc'],
        $request->rules()
    );

    expect($validator->passes())->toBeTrue();
});

test('FilterJobRequest validates per_page parameter', function () {
    $request = new FilterJobRequest;
    $validator = Validator::make(
        ['per_page' => 20],
        $request->rules()
    );

    expect($validator->passes())->toBeTrue();
});

test('FilterJobRequest fails validation for invalid sort field', function () {
    $request = new FilterJobRequest;
    $validator = Validator::make(
        ['sort' => 'invalid_field'],
        $request->rules()
    );

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->has('sort'))->toBeTrue();
});

test('FilterJobRequest fails validation for invalid order direction', function () {
    $request = new FilterJobRequest;
    $validator = Validator::make(
        ['order' => 'invalid'],
        $request->rules()
    );

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->has('order'))->toBeTrue();
});

test('FilterJobRequest fails validation for invalid per_page value', function () {
    $request = new FilterJobRequest;
    $validator = Validator::make(
        ['per_page' => 101],
        $request->rules()
    );

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->has('per_page'))->toBeTrue();
});
