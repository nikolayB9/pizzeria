<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Street>
 */
class StreetFactory extends Factory
{
    /**
     * Данные по умолчанию для создания улицы (без city_id).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->streetName,
        ];
    }
}
