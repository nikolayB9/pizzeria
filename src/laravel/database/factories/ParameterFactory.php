<?php

namespace Database\Factories;

use App\Enums\Parameter\ParameterGroupEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Parameter>
 */
class ParameterFactory extends Factory
{
    /**
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->word()),
            'unit' => fake()->randomElement([null, ucfirst(fake()->randomLetter)]),
            'group' => fake()->randomElement(ParameterGroupEnum::cases())->value,
        ];
    }
}
