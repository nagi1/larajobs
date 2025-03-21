<?php

namespace Database\Factories;

use App\Enums\AttributeType;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    public function definition(): array
    {
        $type = fake()->randomElement(AttributeType::cases());
        $options = null;

        if ($type === AttributeType::SELECT) {
            $options = fake()->randomElements([
                'Option 1',
                'Option 2',
                'Option 3',
                'Option 4',
            ], fake()->numberBetween(2, 4));
        }

        return [
            'name' => fake()->unique()->word(),
            'type' => $type,
            'options' => $options,
        ];
    }
}
