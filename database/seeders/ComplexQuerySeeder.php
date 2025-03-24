<?php

namespace Database\Seeders;

use App\Enums\JobStatus;
use App\Models\Attribute;
use App\Models\JobPost;
use App\Models\Language;
use App\Models\Location;
use Illuminate\Database\Seeder;

class ComplexQuerySeeder extends Seeder
{
    public function run(): void
    {
        // Create or get locations
        $newYork = Location::firstOrCreate(['city' => 'New York', 'state' => 'NY', 'country' => 'USA']);
        $remote = Location::firstOrCreate(['city' => 'Remote', 'state' => 'Remote', 'country' => 'Remote']);

        // Create or get languages
        $php = Language::firstOrCreate(['name' => 'PHP']);
        $javascript = Language::firstOrCreate(['name' => 'JavaScript']);

        // Create or get years_experience attribute
        $yearsExperience = Attribute::firstOrCreate([
            'name' => 'years_experience',
            'type' => 'number',
        ]);

        // Create job posts that match the criteria
        $jobs = [
            [
                'title' => 'Senior Full Stack Developer',
                'description' => 'We are looking for a Senior Full Stack Developer with expertise in PHP and JavaScript. Join our team in New York and help us build amazing web applications.',
                'company_name' => 'TechCorp Inc.',
                'job_type' => 'full-time',
                'is_remote' => false,
                'salary_min' => 120000,
                'salary_max' => 180000,
                'locations' => [$newYork],
                'languages' => [$php, $javascript],
                'years_experience' => 5,
            ],
            [
                'title' => 'Remote PHP Developer',
                'description' => 'Join our fully remote team as a PHP Developer. Help us build and maintain our PHP-based applications.',
                'company_name' => 'Remote Solutions LLC',
                'job_type' => 'full-time',
                'is_remote' => true,
                'salary_min' => 90000,
                'salary_max' => 140000,
                'locations' => [$remote],
                'languages' => [$php],
                'years_experience' => 3,
            ],
            [
                'title' => 'Frontend Developer',
                'description' => 'We are seeking a Frontend Developer with strong JavaScript skills to join our New York office. Help us create beautiful and responsive user interfaces.',
                'company_name' => 'WebDesign Co.',
                'job_type' => 'full-time',
                'is_remote' => false,
                'salary_min' => 100000,
                'salary_max' => 150000,
                'locations' => [$newYork],
                'languages' => [$javascript],
                'years_experience' => 4,
            ],
        ];

        foreach ($jobs as $jobData) {
            $job = JobPost::factory()->create([
                'title' => $jobData['title'],
                'description' => $jobData['description'],
                'company_name' => $jobData['company_name'],
                'job_type' => $jobData['job_type'],
                'is_remote' => $jobData['is_remote'],
                'salary_min' => $jobData['salary_min'],
                'salary_max' => $jobData['salary_max'],
                'status' => JobStatus::PUBLISHED,
            ]);

            // Attach locations
            $job->locations()->attach($jobData['locations']);

            // Attach languages
            $job->languages()->attach($jobData['languages']);

            // Set years_experience attribute
            $job->jobAttributeValues()->create([
                'attribute_id' => $yearsExperience->id,
                'value' => $jobData['years_experience'],
            ]);
        }

        // Create some jobs that should NOT match the criteria
        $nonMatchingJobs = [
            [
                'title' => 'Part-time PHP Developer',
                'description' => 'Looking for a part-time PHP Developer to help with our web applications.',
                'company_name' => 'SmallBiz Solutions',
                'job_type' => 'part-time',
                'is_remote' => false,
                'salary_min' => 40000,
                'salary_max' => 60000,
                'locations' => [$newYork],
                'languages' => [$php],
                'years_experience' => 5,
            ],
            [
                'title' => 'Full-time Python Developer',
                'description' => 'Join our team as a Python Developer in New York. Help us build and maintain our Python applications.',
                'company_name' => 'DataScience Corp',
                'job_type' => 'full-time',
                'is_remote' => false,
                'salary_min' => 90000,
                'salary_max' => 130000,
                'locations' => [$newYork],
                'languages' => [],
                'years_experience' => 3,
            ],
            [
                'title' => 'Full-time Developer with Multiple Locations',
                'description' => 'Looking for a developer who can work from both our New York office and remotely.',
                'company_name' => 'Hybrid Tech Inc.',
                'job_type' => 'full-time',
                'is_remote' => false,
                'salary_min' => 100000,
                'salary_max' => 160000,
                'locations' => [$newYork, $remote],
                'languages' => [$php],
                'years_experience' => 4,
            ],
            [
                'title' => 'Junior Full-time Developer',
                'description' => 'Great opportunity for a junior developer to join our team in New York.',
                'company_name' => 'StartupCo',
                'job_type' => 'full-time',
                'is_remote' => false,
                'salary_min' => 60000,
                'salary_max' => 80000,
                'locations' => [$newYork],
                'languages' => [$php],
                'years_experience' => 2,
            ],
        ];

        foreach ($nonMatchingJobs as $jobData) {
            $job = JobPost::factory()->create([
                'title' => $jobData['title'],
                'description' => $jobData['description'],
                'company_name' => $jobData['company_name'],
                'job_type' => $jobData['job_type'],
                'is_remote' => $jobData['is_remote'],
                'salary_min' => $jobData['salary_min'],
                'salary_max' => $jobData['salary_max'],
                'status' => JobStatus::PUBLISHED,
            ]);

            // Attach locations
            $job->locations()->attach($jobData['locations']);

            // Attach languages
            $job->languages()->attach($jobData['languages']);

            // Set years_experience attribute
            $job->jobAttributeValues()->create([
                'attribute_id' => $yearsExperience->id,
                'value' => $jobData['years_experience'],
            ]);
        }
    }
}
