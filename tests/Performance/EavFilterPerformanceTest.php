<?php

namespace Tests\Performance;

use App\Enums\AttributeType;
use App\Filters\EavFilter;
use App\Models\Attribute;
use App\Models\JobAttributeValue;
use App\Models\JobPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EavFilterPerformanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up test data
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a large dataset for performance testing
        // This will be used by all test methods
        $this->createTestData();
    }

    /**
     * Test the performance of the EAV filter with text attributes
     */
    public function test_text_filter_performance()
    {
        $attribute = Attribute::where('name', 'programming_languages')->first();
        $filter = new EavFilter;

        $numIterations = 10;
        $totalTime = 0;

        // Warm up query cache
        $query = JobPost::query();
        $filter->apply($query, [
            'name' => 'programming_languages',
            'value' => [
                'text' => 'javascript',
                'mode' => 'contains',
            ],
        ])->get();

        // Run test
        for ($i = 0; $i < $numIterations; $i++) {
            $query = JobPost::query();

            $startTime = microtime(true);

            $results = $filter->apply($query, [
                'name' => 'programming_languages',
                'value' => [
                    'text' => 'javascript',
                    'mode' => 'contains',
                ],
            ])->get();

            $endTime = microtime(true);
            $totalTime += ($endTime - $startTime);
        }

        $averageTime = $totalTime / $numIterations;
        $resultCount = $results->count();

        // Output results
        $this->outputPerformanceMetrics('Text Filter (contains mode)', $averageTime, $resultCount, $numIterations);

        // Assert that the query is fast enough (adjust threshold as needed)
        $this->assertLessThan(0.1, $averageTime, 'Text filter performance exceeds threshold');
    }

    /**
     * Test the performance of the EAV filter with number range attributes
     */
    public function test_number_range_filter_performance()
    {
        $attribute = Attribute::where('name', 'years_experience')->first();
        $filter = new EavFilter;

        $numIterations = 10;
        $totalTime = 0;

        // Warm up query cache
        $query = JobPost::query();
        $filter->apply($query, [
            'name' => 'years_experience',
            'value' => [
                'min' => 3,
                'max' => 7,
            ],
        ])->get();

        // Run test
        for ($i = 0; $i < $numIterations; $i++) {
            $query = JobPost::query();

            $startTime = microtime(true);

            $results = $filter->apply($query, [
                'name' => 'years_experience',
                'value' => [
                    'min' => 3,
                    'max' => 7,
                ],
            ])->get();

            $endTime = microtime(true);
            $totalTime += ($endTime - $startTime);
        }

        $averageTime = $totalTime / $numIterations;
        $resultCount = $results->count();

        // Output results
        $this->outputPerformanceMetrics('Number Range Filter', $averageTime, $resultCount, $numIterations);

        // Assert that the query is fast enough (adjust threshold as needed)
        $this->assertLessThan(0.1, $averageTime, 'Number range filter performance exceeds threshold');
    }

    /**
     * Test the performance of the EAV filter with select attributes in 'all' mode
     */
    public function test_select_all_filter_performance()
    {
        $attribute = Attribute::where('name', 'skills')->first();
        $filter = new EavFilter;

        $numIterations = 10;
        $totalTime = 0;

        // Warm up query cache
        $query = JobPost::query();
        $filter->apply($query, [
            'name' => 'skills',
            'value' => [
                'values' => ['php', 'javascript'],
                'mode' => 'all',
            ],
        ])->get();

        // Run test
        for ($i = 0; $i < $numIterations; $i++) {
            $query = JobPost::query();

            $startTime = microtime(true);

            $results = $filter->apply($query, [
                'name' => 'skills',
                'value' => [
                    'values' => ['php', 'javascript'],
                    'mode' => 'all',
                ],
            ])->get();

            $endTime = microtime(true);
            $totalTime += ($endTime - $startTime);
        }

        $averageTime = $totalTime / $numIterations;
        $resultCount = $results->count();

        // Output results
        $this->outputPerformanceMetrics('Select All Filter', $averageTime, $resultCount, $numIterations);

        // Assert that the query is fast enough (adjust threshold as needed)
        $this->assertLessThan(0.1, $averageTime, 'Select All filter performance exceeds threshold');
    }

    /**
     * Test the performance of the EAV filter with date range attributes
     */
    public function test_date_range_filter_performance()
    {
        $attribute = Attribute::where('name', 'available_from')->first();
        $filter = new EavFilter;

        $numIterations = 10;
        $totalTime = 0;

        // Warm up query cache
        $query = JobPost::query();
        $filter->apply($query, [
            'name' => 'available_from',
            'value' => [
                'from' => '2023-01-01',
                'to' => '2023-06-30',
            ],
        ])->get();

        // Run test
        for ($i = 0; $i < $numIterations; $i++) {
            $query = JobPost::query();

            $startTime = microtime(true);

            $results = $filter->apply($query, [
                'name' => 'available_from',
                'value' => [
                    'from' => '2023-01-01',
                    'to' => '2023-06-30',
                ],
            ])->get();

            $endTime = microtime(true);
            $totalTime += ($endTime - $startTime);
        }

        $averageTime = $totalTime / $numIterations;
        $resultCount = $results->count();

        // Output results
        $this->outputPerformanceMetrics('Date Range Filter', $averageTime, $resultCount, $numIterations);

        // Assert that the query is fast enough (adjust threshold as needed)
        $this->assertLessThan(0.1, $averageTime, 'Date range filter performance exceeds threshold');
    }

    /**
     * Helper to output performance metrics
     */
    private function outputPerformanceMetrics(string $testName, float $averageTime, int $resultCount, int $iterations): void
    {
        echo "\n";
        echo "Performance Test: {$testName}\n";
        echo "---------------------------------------\n";
        echo "Iterations: {$iterations}\n";
        echo 'Average Time: '.number_format($averageTime * 1000, 2)."ms\n";
        echo "Results Count: {$resultCount}\n";
        echo "---------------------------------------\n";
    }

    /**
     * Create test data for performance testing
     */
    private function createTestData(): void
    {
        // Skip if data already exists
        if (JobPost::count() > 100) {
            return;
        }

        echo "\nCreating test data for performance testing...\n";

        // Begin transaction for faster inserts
        DB::beginTransaction();

        try {
            // Create attributes
            $textAttribute = Attribute::create([
                'name' => 'programming_languages',
                'type' => AttributeType::TEXT,
            ]);

            $numberAttribute = Attribute::create([
                'name' => 'years_experience',
                'type' => AttributeType::NUMBER,
            ]);

            $selectAttribute = Attribute::create([
                'name' => 'skills',
                'type' => AttributeType::SELECT,
                'options' => json_encode(['php', 'javascript', 'python', 'java', 'ruby', 'c#', 'go', 'rust', 'html', 'css']),
            ]);

            $dateAttribute = Attribute::create([
                'name' => 'available_from',
                'type' => AttributeType::DATE,
            ]);

            // Create 500 job posts with attribute values
            $languages = ['PHP, Laravel, MySQL', 'JavaScript, React, Node.js', 'Python, Django, PostgreSQL',
                'Java, Spring, Oracle', 'Ruby, Rails, PostgreSQL', 'C#, .NET, SQL Server',
                'Go, MongoDB', 'Rust, WebAssembly'];

            $skills = [
                json_encode(['php', 'mysql', 'laravel']),
                json_encode(['javascript', 'react', 'node']),
                json_encode(['python', 'django', 'postgresql']),
                json_encode(['java', 'spring', 'oracle']),
                json_encode(['ruby', 'rails', 'postgresql']),
                json_encode(['c#', '.net', 'sql server']),
                json_encode(['php', 'javascript', 'mysql']),
                json_encode(['python', 'javascript', 'mongodb']),
                json_encode(['java', 'javascript', 'oracle']),
                json_encode(['php', 'javascript', 'html', 'css']),
            ];

            for ($i = 0; $i < 500; $i++) {
                $jobPost = JobPost::factory()->create();

                // Add text attribute
                JobAttributeValue::create([
                    'job_post_id' => $jobPost->id,
                    'attribute_id' => $textAttribute->id,
                    'value' => $languages[array_rand($languages)],
                ]);

                // Add number attribute
                JobAttributeValue::create([
                    'job_post_id' => $jobPost->id,
                    'attribute_id' => $numberAttribute->id,
                    'value' => rand(1, 10),
                ]);

                // Add select attribute
                JobAttributeValue::create([
                    'job_post_id' => $jobPost->id,
                    'attribute_id' => $selectAttribute->id,
                    'value' => $skills[array_rand($skills)],
                ]);

                // Add date attribute
                $year = rand(2022, 2024);
                $month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
                $day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);

                JobAttributeValue::create([
                    'job_post_id' => $jobPost->id,
                    'attribute_id' => $dateAttribute->id,
                    'value' => "{$year}-{$month}-{$day}",
                ]);
            }

            DB::commit();
            echo "Test data created successfully.\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo 'Error creating test data: '.$e->getMessage()."\n";
        }
    }
}
