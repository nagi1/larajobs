<?php

namespace Database\Seeders;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\JobPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobAttributeValueSeeder extends Seeder
{
    private array $attributeValues = [
        'years_experience' => [
            'junior' => [1, 3],
            'mid' => [3, 5],
            'senior' => [5, 8],
            'lead' => [8, 12],
            'manager' => [10, 15],
        ],
        'education_level' => [
            'junior' => ['Bachelor', 'Associate'],
            'mid' => ['Bachelor', 'Master'],
            'senior' => ['Bachelor', 'Master', 'PhD'],
            'lead' => ['Bachelor', 'Master', 'PhD'],
            'manager' => ['Bachelor', 'Master', 'PhD'],
        ],
        'required_skills' => [
            'junior' => ['Basic', 'Intermediate'],
            'mid' => ['Intermediate', 'Advanced'],
            'senior' => ['Advanced', 'Expert'],
            'lead' => ['Expert', 'Master'],
            'manager' => ['Expert', 'Master'],
        ],
        'team_size' => [
            'junior' => [1, 5],
            'mid' => [5, 10],
            'senior' => [5, 15],
            'lead' => [10, 20],
            'manager' => [15, 50],
        ],
        'travel_required' => [
            'junior' => [false],
            'mid' => [false, true],
            'senior' => [false, true],
            'lead' => [true],
            'manager' => [true],
        ],
        'on_call_required' => [
            'junior' => [false],
            'mid' => [false, true],
            'senior' => [true],
            'lead' => [true],
            'manager' => [true],
        ],
        'start_date' => [
            'junior' => ['Immediate', 'Within 2 weeks', 'Within 1 month'],
            'mid' => ['Within 2 weeks', 'Within 1 month', 'Within 2 months'],
            'senior' => ['Within 1 month', 'Within 2 months', 'Within 3 months'],
            'lead' => ['Within 2 months', 'Within 3 months'],
            'manager' => ['Within 2 months', 'Within 3 months'],
        ],
        'work_schedule' => [
            'junior' => ['Regular', 'Flexible'],
            'mid' => ['Regular', 'Flexible'],
            'senior' => ['Flexible', 'Core Hours'],
            'lead' => ['Flexible', 'Core Hours'],
            'manager' => ['Flexible', 'Core Hours'],
        ],
        'benefits' => [
            'junior' => ['Health Insurance', '401k', 'Paid Time Off'],
            'mid' => ['Health Insurance', '401k', 'Paid Time Off', 'Stock Options'],
            'senior' => ['Health Insurance', '401k', 'Paid Time Off', 'Stock Options', 'Annual Bonus'],
            'lead' => ['Health Insurance', '401k', 'Paid Time Off', 'Stock Options', 'Annual Bonus', 'Relocation Assistance'],
            'manager' => ['Health Insurance', '401k', 'Paid Time Off', 'Stock Options', 'Annual Bonus', 'Relocation Assistance', 'Executive Benefits'],
        ],
        'interview_process' => [
            'junior' => ['Technical Screening', 'Technical Interview', 'Team Interview'],
            'mid' => ['Technical Screening', 'Technical Interview', 'Team Interview', 'System Design'],
            'senior' => ['Technical Screening', 'Technical Interview', 'Team Interview', 'System Design', 'Leadership Interview'],
            'lead' => ['Technical Screening', 'Technical Interview', 'Team Interview', 'System Design', 'Leadership Interview', 'Executive Interview'],
            'manager' => ['Technical Screening', 'Team Interview', 'System Design', 'Leadership Interview', 'Executive Interview', 'Culture Fit'],
        ],
    ];

    public function run(): void
    {
        $attributes = Attribute::all()->keyBy('name');
        $jobPosts = JobPost::all();
        $attributeValues = [];

        foreach ($jobPosts as $jobPost) {
            $level = $this->getJobLevel($jobPost->title);

            foreach ($this->attributeValues as $attributeName => $levelValues) {
                if (! isset($attributes[$attributeName])) {
                    continue;
                }

                $attribute = $attributes[$attributeName];
                $value = $this->generateAttributeValue($attribute, $levelValues[$level]);

                $attributeValues[] = [
                    'job_post_id' => $jobPost->id,
                    'attribute_id' => $attribute->id,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Bulk insert in chunks to avoid memory issues
        foreach (array_chunk($attributeValues, 1000) as $chunk) {
            DB::table('job_attribute_values')->insert($chunk);
        }
    }

    private function getJobLevel(string $title): string
    {
        $title = strtolower($title);

        if (str_contains($title, 'junior') || str_contains($title, 'associate')) {
            return 'junior';
        }

        if (str_contains($title, 'senior') || str_contains($title, 'staff')) {
            return 'senior';
        }

        if (str_contains($title, 'lead') || str_contains($title, 'principal')) {
            return 'lead';
        }

        if (str_contains($title, 'manager') || str_contains($title, 'director')) {
            return 'manager';
        }

        return 'mid';
    }

    private function generateAttributeValue($attribute, $possibleValues): string
    {
        return match ($attribute->type) {
            AttributeType::TEXT => fake()->randomElement($possibleValues),
            AttributeType::NUMBER => (string) fake()->numberBetween($possibleValues[0], $possibleValues[1]),
            AttributeType::BOOLEAN => (string) fake()->randomElement($possibleValues),
            AttributeType::DATE => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            AttributeType::SELECT => fake()->randomElement($possibleValues),
        };
    }
}
