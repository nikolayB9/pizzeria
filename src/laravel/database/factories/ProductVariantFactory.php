<?php

namespace Database\Factories;

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
            'name' => rand(1, 100) . ' ' . fake()->randomLetter,
            'price' => rand(90, 900),
            'old_price' => null,
        ];
    }
}
