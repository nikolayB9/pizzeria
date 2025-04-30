<?php

namespace Database\Seeders;

use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->createAdmin();
        $this->createUser();
    }

    private function createAdmin(): void
    {
        if (!User::where('email', 'admin@mail.ru')->exists()) {
            User::create([
                'name' => 'Admin_1',
                'phone_number' => '+79876543210',
                'email' => 'admin@mail.ru',
                'email_verified_at' => now(),
                'password' => '123123123',
                'role' => UserRoleEnum::Admin->value,
                'birth_date' => fake()->randomElement([null, fake()->dateTimeBetween('-70 years', '-16 years')->format('Y-m-d')]),
                'remember_token' => Str::random(10),
            ]);
        }
    }

    private function createUser(): void
    {
        if (!User::where('email', 'user@mail.ru')->exists()) {
            User::create([
                'name' => 'User_1',
                'phone_number' => '+79012345678',
                'email' => 'user@mail.ru',
                'email_verified_at' => now(),
                'password' => '123123123',
                'role' => UserRoleEnum::User->value,
                'birth_date' => fake()->randomElement([null, fake()->dateTimeBetween('-70 years', '-16 years')->format('Y-m-d')]),
                'remember_token' => Str::random(10),
            ]);
        }
    }
}
