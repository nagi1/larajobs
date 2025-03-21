<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Development
            'Frontend Development',
            'Backend Development',
            'Full Stack Development',
            'Mobile Development',
            'DevOps',
            'Cloud Computing',
            'Database Development',
            'API Development',
            'Game Development',
            'Embedded Systems',

            // Design & UX
            'UI/UX Design',
            'Product Design',
            'Graphic Design',
            'Interaction Design',
            'Visual Design',

            // Data & Analytics
            'Data Science',
            'Data Engineering',
            'Data Analysis',
            'Machine Learning',
            'Artificial Intelligence',
            'Business Intelligence',

            // Management & Leadership
            'Engineering Management',
            'Technical Leadership',
            'Product Management',
            'Project Management',
            'Scrum Master',
            'Agile Coach',

            // Quality Assurance
            'Quality Assurance',
            'Test Automation',
            'Performance Testing',
            'Security Testing',

            // Security
            'Information Security',
            'Cybersecurity',
            'Security Engineering',
            'Penetration Testing',

            // Other Tech Roles
            'Technical Writing',
            'Technical Support',
            'System Administration',
            'Network Engineering',
            'Blockchain Development',
            'AR/VR Development',
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
