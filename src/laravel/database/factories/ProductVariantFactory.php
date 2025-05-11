<?php

namespace Database\Factories;

use App\Models\Parameter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => strtolower(fake()->unique()->regexify('[0-9]{1,2}[A-Z]{1,2}')),
            'price' => rand(90, 900),
            'old_price' => null,
        ];
    }

    public function withParameters(int $count = 4): ProductVariantFactory|Factory
    {
        return $this->hasAttached(
            Parameter::factory()->count($count),
            fn() => [
                'value' => fake()->randomElement([fake()->word(), rand(1, 100)]),
                'is_shared' => fake()->boolean(),
            ],
        );
    }
}
