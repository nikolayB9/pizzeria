<?php

namespace Database\Seeders;

use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    private int $userCount = 3;

    public function run(): void
    {
        $this->updateOrCreateAdmin();
        $this->createUsers();
    }

    private function updateOrCreateAdmin(): void
    {
        User::updateOrCreate(
            ['phone_number' => '+79876543210', 'email' => 'admin@mail.ru'],
            [
                'name' => 'Admin_1',
                'password' => '123123123',
                'role' => UserRoleEnum::Admin->value,
                'birth_date' => fake()->randomElement([null, fake()->dateTimeBetween('-70 years', '-16 years')->format('Y-m-d')]),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);
    }

    private function createUsers(): void
    {
        User::factory($this->userCount)->create();
    }
}
