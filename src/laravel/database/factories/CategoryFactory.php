<?php

namespace Database\Factories;

use App\Enums\Category\CategoryTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categoryName = ucfirst(fake()->unique()->words(rand(1, 2), true));

        return [
            'name' => $categoryName,
            'slug' => Str::slug($categoryName),
            'type' => CategoryTypeEnum::ProductType->value,
        ];
    }
}
