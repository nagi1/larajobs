<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'PHP',
                'JavaScript',
                'Python',
                'Java',
                'Ruby',
                'Go',
                'Rust',
                'TypeScript',
                'Swift',
                'Kotlin',
            ]),
        ];
    }
}
