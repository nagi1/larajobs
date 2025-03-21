<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Backend Development',
                'Frontend Development',
                'Full Stack Development',
                'DevOps',
                'Mobile Development',
                'Data Science',
                'Machine Learning',
                'UI/UX Design',
                'Product Management',
                'QA Engineering',
            ]),
        ];
    }
}
