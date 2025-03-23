<?php

namespace Database\Seeders;

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Models\Category;
use App\Models\JobPost;
use App\Models\Language;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobPostSeeder extends Seeder
{
    private array $techCompanies = [
        'Google' => ['Mountain View', 'New York', 'London'],
        'Microsoft' => ['Bellevue', 'Seattle', 'Dublin'],
        'Apple' => ['Palo Alto', 'San Francisco', 'London'],
        'Amazon' => ['Seattle', 'New York', 'Dublin'],
        'Meta' => ['San Francisco', 'New York', 'London'],
        'Netflix' => ['San Francisco', 'London', 'Tokyo'],
        'Uber' => ['San Francisco', 'New York', 'Amsterdam'],
        'Airbnb' => ['San Francisco', 'London', 'Singapore'],
        'Twitter' => ['San Francisco', 'New York', 'London'],
        'LinkedIn' => ['San Francisco', 'Dublin', 'Singapore'],
        'Stripe' => ['San Francisco', 'Dublin', 'Singapore'],
        'Square' => ['San Francisco', 'New York', 'Toronto'],
        'Shopify' => ['Toronto', 'Montreal', 'London'],
        'Adobe' => ['San Jose', 'New York', 'London'],
        'Salesforce' => ['San Francisco', 'New York', 'London'],
        'IBM' => ['New York', 'Toronto', 'London'],
        'Intel' => ['San Jose', 'Austin', 'Bangalore'],
        'Oracle' => ['Austin', 'Bangalore', 'London'],
        'Samsung' => ['Seoul', 'San Jose', 'London'],
        'Sony' => ['Tokyo', 'San Francisco', 'London'],
    ];

    private array $salaryRanges = [
        'junior' => [
            'min' => 50000,
            'max' => 80000,
        ],
        'mid' => [
            'min' => 80000,
            'max' => 120000,
        ],
        'senior' => [
            'min' => 120000,
            'max' => 200000,
        ],
        'lead' => [
            'min' => 150000,
            'max' => 250000,
        ],
        'manager' => [
            'min' => 180000,
            'max' => 300000,
        ],
    ];

    private array $statusDistribution = [
        JobStatus::PUBLISHED->value => 80, // 80% published
        JobStatus::DRAFT->value => 15,     // 15% draft
        JobStatus::ARCHIVED->value => 5,   // 5% archived
    ];

    public function run(): void
    {
        // Pre-load all relationships
        $locations = Location::all()->keyBy('city');
        $languages = Language::all();
        $categories = Category::all();

        // Prepare bulk insert arrays for relationships
        $jobLocations = [];
        $jobLanguages = [];
        $jobCategories = [];

        // Create 5000 jobs in larger batches
        $batchSize = 500;
        for ($i = 0; $i < 10; $i++) {
            $jobs = [];
            $now = now();

            for ($j = 0; $j < $batchSize; $j++) {
                $company = array_rand($this->techCompanies);
                $level = array_rand($this->salaryRanges);
                $salaryRange = $this->salaryRanges[$level];
                $jobId = ($i * $batchSize) + $j + 1;

                // Determine job status based on distribution
                $status = $this->getRandomStatus();
                $publishedAt = $status === JobStatus::PUBLISHED->value
                    ? Carbon::now()->subDays(fake()->numberBetween(1, 30))
                    : null;

                $jobs[] = [
                    'title' => $this->generateJobTitle($level),
                    'description' => fake()->paragraphs(5, true),
                    'company_name' => $company,
                    'salary_min' => fake()->numberBetween($salaryRange['min'], $salaryRange['min'] + 20000),
                    'salary_max' => fake()->numberBetween($salaryRange['max'] - 20000, $salaryRange['max']),
                    'is_remote' => fake()->boolean(30),
                    'job_type' => fake()->randomElement(JobType::cases())->value,
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Prepare location relationships
                $companyLocations = $this->techCompanies[$company];
                $numLocations = fake()->numberBetween(1, 3);
                foreach ($companyLocations as $city) {
                    if (isset($locations[$city])) {
                        $jobLocations[] = [
                            'job_post_id' => $jobId,
                            'location_id' => $locations[$city]->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                // Prepare language relationships
                $numLanguages = fake()->numberBetween(2, 5);
                $selectedLanguages = $languages->random($numLanguages);
                foreach ($selectedLanguages as $language) {
                    $jobLanguages[] = [
                        'job_post_id' => $jobId,
                        'language_id' => $language->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Prepare category relationships
                $numCategories = fake()->numberBetween(1, 3);
                $selectedCategories = $categories->random($numCategories);
                foreach ($selectedCategories as $category) {
                    $jobCategories[] = [
                        'job_post_id' => $jobId,
                        'category_id' => $category->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Bulk insert jobs
            JobPost::insert($jobs);

            // Bulk insert relationships in chunks to avoid memory issues
            foreach (array_chunk($jobLocations, 1000) as $chunk) {
                DB::table('job_post_location')->insert($chunk);
            }
            foreach (array_chunk($jobLanguages, 1000) as $chunk) {
                DB::table('job_post_language')->insert($chunk);
            }
            foreach (array_chunk($jobCategories, 1000) as $chunk) {
                DB::table('category_job_post')->insert($chunk);
            }

            // Clear arrays to free memory
            $jobLocations = [];
            $jobLanguages = [];
            $jobCategories = [];
        }
    }

    private function generateJobTitle(string $level): string
    {
        $positions = [
            'Software Engineer',
            'Full Stack Developer',
            'Backend Developer',
            'Frontend Developer',
            'DevOps Engineer',
            'Data Engineer',
            'Machine Learning Engineer',
            'Mobile Developer',
            'QA Engineer',
            'Security Engineer',
        ];

        $prefixes = [
            'junior' => ['Junior', 'Associate'],
            'mid' => ['', 'Mid-Level'],
            'senior' => ['Senior', 'Staff'],
            'lead' => ['Lead', 'Principal'],
            'manager' => ['Engineering Manager', 'Technical Lead'],
        ];

        $position = fake()->randomElement($positions);
        $prefix = fake()->randomElement($prefixes[$level]);

        return $prefix ? "$prefix $position" : $position;
    }

    private function getRandomStatus(): string
    {
        $rand = fake()->numberBetween(1, 100);
        $sum = 0;

        foreach ($this->statusDistribution as $status => $percentage) {
            $sum += $percentage;
            if ($rand <= $sum) {
                return $status;
            }
        }

        return JobStatus::DRAFT->value; // fallback
    }
}
