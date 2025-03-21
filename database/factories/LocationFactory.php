<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => fake()->country(),
        ];
    }
}
