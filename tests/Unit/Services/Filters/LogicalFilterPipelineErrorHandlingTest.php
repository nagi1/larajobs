<?php

use App\Exceptions\FilterException;
use App\Filters\IsRemoteFilter;
use App\Filters\JobTypeFilter;
use App\Filters\StatusFilter;
use App\Services\Filters\LogicalFilterPipeline;
use Illuminate\Database\Eloquent\Builder;

beforeEach(function () {
    // Create a pipeline with some filters
    $this->pipeline = new LogicalFilterPipeline;
    $this->pipeline->addFilter(new JobTypeFilter);
    $this->pipeline->addFilter(new IsRemoteFilter);
    $this->pipeline->addFilter(new StatusFilter);

    // Create a mock query builder
    $this->query = Mockery::mock(Builder::class);
    $this->query->shouldReceive('where')->andReturnSelf();
});

// The LogicalFilterPipeline doesn't check for invalid operators in the process method;
// it only checks for 'and' or 'or' keys and then lets validateLogicalOperation handle validation
// These tests ensure the validation is working correctly

test('throws exception when both and/or are specified', function () {
    $filters = [
        'and' => [
            ['job_type' => 'full-time'],
            ['is_remote' => true],
        ],
        'or' => [
            ['job_type' => 'part-time'],
            ['is_remote' => false],
        ],
    ];

    expect(fn () => $this->pipeline->process($this->query, $filters))
        ->toThrow(FilterException::class, 'Cannot use both "and" and "or" operations at the same level');
});

test('throws exception when conditions is not an array', function () {
    $filters = [
        'and' => 'not an array',
    ];

    expect(fn () => $this->pipeline->process($this->query, $filters))
        ->toThrow(FilterException::class, "Conditions for 'and' operation must be an array");
});

test('throws exception when there are less than 2 conditions', function () {
    $filters = [
        'and' => [
            ['job_type' => 'full-time'],
        ],
    ];

    expect(fn () => $this->pipeline->process($this->query, $filters))
        ->toThrow(FilterException::class, "At least 2 conditions are required for 'and' operation");
});

test('throws exception when a condition is not an array', function () {
    $filters = [
        'and' => [
            ['job_type' => 'full-time'],
            'not an array',
        ],
    ];

    expect(fn () => $this->pipeline->process($this->query, $filters))
        ->toThrow(FilterException::class, 'Each condition must be an array');
});

test('throws exception when a nested condition has both and/or operators', function () {
    $filters = [
        'and' => [
            ['job_type' => 'full-time'],
            [
                'and' => [
                    ['is_remote' => true],
                ],
                'or' => [
                    ['status' => 'published'],
                ],
            ],
        ],
    ];

    expect(fn () => $this->pipeline->process($this->query, $filters))
        ->toThrow(FilterException::class, 'Cannot use both "and" and "or" operations at the same level');
});

test('gracefully handles unknown filters', function () {
    $filters = [
        'unknown_filter' => 'value',
    ];

    // Should not throw an exception
    $result = $this->pipeline->process($this->query, $filters);

    // Should return the query unchanged
    expect($result)->toBe($this->query);
});

test('gracefully handles empty filters', function () {
    $filters = [];

    // Should not throw an exception
    $result = $this->pipeline->process($this->query, $filters);

    // Should return the query unchanged
    expect($result)->toBe($this->query);
});

test('gracefully handles null filters', function () {
    $filters = null;

    // Should not throw an exception
    $result = $this->pipeline->process($this->query, $filters);

    // Should return the query unchanged
    expect($result)->toBe($this->query);
});
