<?php

namespace Database\Seeders;

use App\Enums\JobType;
use App\Models\Attribute;
use App\Models\JobAttributeValue;
use App\Models\JobPost;
use Illuminate\Database\Seeder;

class JobAttributeValueSeeder extends Seeder
{
    public function run(): void
    {
        $jobs = JobPost::all();
        $attributes = Attribute::all();

        foreach ($jobs as $job) {
            // 10% chance of having no attributes at all
            if (rand(1, 100) <= 10) {
                continue;
            }

            // Assign attributes based on job type
            $this->assignAttributesByJobType($job, $attributes);
        }
    }

    private function assignAttributesByJobType(JobPost $job, $attributes): void
    {
        // 70% chance of having common attributes
        if (rand(1, 100) <= 70) {
            $this->assignCommonAttributes($job, $attributes);
        }

        // Type-specific attributes based on job type
        match ($job->job_type) {
            JobType::FULL_TIME->value => $this->assignFullTimeAttributes($job, $attributes),
            JobType::PART_TIME->value => $this->assignPartTimeAttributes($job, $attributes),
            JobType::CONTRACT->value => $this->assignContractAttributes($job, $attributes),
            JobType::FREELANCE->value => $this->assignFreelanceAttributes($job, $attributes),
            default => null,
        };
    }

    private function assignCommonAttributes(JobPost $job, $attributes): void
    {
        // Years of experience (80% chance)
        if (rand(1, 100) <= 80) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'years_experience'),
                rand(1, 10)
            );
        }

        // Required skills (90% chance)
        if (rand(1, 100) <= 90) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'required_skills'),
                ['Basic', 'Intermediate', 'Advanced', 'Expert', 'Master'][rand(0, 4)]
            );
        }

        // Remote work policy (85% chance)
        if (rand(1, 100) <= 85) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'remote_work_policy'),
                ['Fully Remote', 'Hybrid', 'On-site', 'Flexible'][rand(0, 3)]
            );
        }

        // Benefits (75% chance)
        if (rand(1, 100) <= 75) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'benefits'),
                ['Health Insurance', '401k', 'Paid Time Off'][rand(0, 2)]
            );
        }
    }

    private function assignFullTimeAttributes(JobPost $job, $attributes): void
    {
        // Vacation days (60% chance)
        if (rand(1, 100) <= 60) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'vacation_days'),
                rand(10, 25)
            );
        }

        // Health insurance type (80% chance)
        if (rand(1, 100) <= 80) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'health_insurance_type'),
                ['Basic', 'Premium', 'Family'][rand(0, 2)]
            );
        }

        // Equity offered (40% chance)
        if (rand(1, 100) <= 40) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'equity_offered'),
                rand(0, 1)
            );
        }

        // Bonus percentage (50% chance)
        if (rand(1, 100) <= 50) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'bonus_percentage'),
                rand(5, 20)
            );
        }
    }

    private function assignPartTimeAttributes(JobPost $job, $attributes): void
    {
        // Work schedule (90% chance)
        if (rand(1, 100) <= 90) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'work_schedule'),
                ['Regular', 'Flexible', 'Core Hours'][rand(0, 2)]
            );
        }

        // Hours per week (85% chance)
        if (rand(1, 100) <= 85) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'hours_per_week'),
                rand(10, 30)
            );
        }

        // Benefits (60% chance)
        if (rand(1, 100) <= 60) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'benefits'),
                ['Health Insurance', 'Paid Time Off'][rand(0, 1)]
            );
        }
    }

    private function assignContractAttributes(JobPost $job, $attributes): void
    {
        // Contract duration (95% chance)
        if (rand(1, 100) <= 95) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'contract_duration'),
                ['3 months', '6 months', '12 months', '24 months', '36 months'][rand(0, 4)]
            );
        }

        // Contract to hire possibility (70% chance)
        if (rand(1, 100) <= 70) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'contract_to_hire'),
                rand(0, 1)
            );
        }

        // Project scope (80% chance)
        if (rand(1, 100) <= 80) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'project_scope'),
                'Specific project with defined deliverables and timeline'
            );
        }

        // Tech stack (75% chance)
        if (rand(1, 100) <= 75) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'tech_stack'),
                'Modern web technologies including React, Laravel, and AWS'
            );
        }
    }

    private function assignFreelanceAttributes(JobPost $job, $attributes): void
    {
        // Project scope (90% chance)
        if (rand(1, 100) <= 90) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'project_scope'),
                'Independent project with flexible timeline'
            );
        }

        // Payment terms (95% chance)
        if (rand(1, 100) <= 95) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'payment_terms'),
                ['Hourly', 'Fixed Price', 'Milestone-based'][rand(0, 2)]
            );
        }

        // Required skills (85% chance)
        if (rand(1, 100) <= 85) {
            $this->createAttributeValue(
                $job,
                $attributes->firstWhere('name', 'required_skills'),
                ['Expert', 'Master'][rand(0, 1)]
            );
        }

        // Remote work policy (always remote for freelance)
        $this->createAttributeValue(
            $job,
            $attributes->firstWhere('name', 'remote_work_policy'),
            'Fully Remote'
        );
    }

    private function createAttributeValue(JobPost $job, Attribute $attribute, mixed $value): void
    {
        if (! $attribute) {
            return;
        }

        JobAttributeValue::create([
            'job_post_id' => $job->id,
            'attribute_id' => $attribute->id,
            'value' => $value,
        ]);
    }
}
