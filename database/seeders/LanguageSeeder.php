<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            // Backend Languages
            'PHP',
            'Python',
            'Java',
            'Ruby',
            'Go',
            'Rust',
            'Node.js',
            'C#',
            'Scala',
            'Kotlin',
            'Swift',
            'TypeScript',

            // Frontend Languages
            'JavaScript',
            'HTML',
            'CSS',
            'Sass',
            'Less',

            // Database Languages
            'SQL',
            'MongoDB',
            'Redis',
            'GraphQL',

            // Mobile Development
            'React Native',
            'Flutter',
            'iOS',
            'Android',

            // DevOps & Tools
            'Docker',
            'Kubernetes',
            'Terraform',
            'Ansible',
            'Jenkins',
            'Git',
            'AWS',
            'Azure',
            'GCP',
        ];

        foreach ($languages as $language) {
            Language::create(['name' => $language]);
        }
    }
}
