<?php

namespace Database\Factories;

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobPostFactory extends Factory
{
    protected $model = JobPost::class;

    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'company_name' => fake()->company(),
            'salary_min' => fake()->numberBetween(30000, 80000),
            'salary_max' => fake()->numberBetween(80000, 200000),
            'is_remote' => fake()->boolean(),
            'job_type' => fake()->randomElement(JobType::cases())->value,
            'status' => fake()->randomElement(JobStatus::cases())->value,
            'published_at' => fake()->optional()->dateTime(),
        ];
    }
}
