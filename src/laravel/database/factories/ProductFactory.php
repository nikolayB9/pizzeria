<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productName = ucfirst(fake()->unique()->words(rand(1, 4), true));

        return [
            'name' => $productName,
            'description' => fake()->sentence(),
            'is_published' => true,
            'slug' => Str::slug($productName),
        ];
    }
}
