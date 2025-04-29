<?php

namespace Database\Factories;

use App\Enums\User\UserRoleEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'phone_number' => fake()->unique()->regexify('(\+7)9[0-9]{9}'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => UserRoleEnum::User->value,
            'birth_date' => fake()->randomElement([null, fake()->dateTimeBetween('-70 years', '-16 years')->format('Y-m-d')]),
            'remember_token' => Str::random(10),
        ];
    }
}
