<?php

namespace Database\Factories;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\JobAttributeValue;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobAttributeValueFactory extends Factory
{
    protected $model = JobAttributeValue::class;

    public function definition(): array
    {
        $attribute = Attribute::factory()->create();
        $value = $this->generateValue($attribute);

        return [
            'job_post_id' => JobPost::factory(),
            'attribute_id' => $attribute,
            'value' => $value,
        ];
    }

    private function generateValue(Attribute $attribute): string
    {
        return match ($attribute->type) {
            AttributeType::TEXT => fake()->sentence(),
            AttributeType::NUMBER => (string) fake()->numberBetween(0, 1000),
            AttributeType::BOOLEAN => fake()->boolean() ? '1' : '0',
            AttributeType::DATE => fake()->date(),
            AttributeType::SELECT => fake()->randomElement($attribute->options),
        };
    }
}
