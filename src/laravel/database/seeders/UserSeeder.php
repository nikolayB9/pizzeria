<?php

namespace Database\Seeders;

use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    private int $userCount = 3;

    public function run(): void
    {
        $this->createAdmin();
        $this->createUsers();
    }

    private function createAdmin(): void
    {
        User::factory()->create([
            'name' => 'Admin_1',
            'phone_number' => '+79876543210',
            'email' => 'admin@mail.ru',
            'password' => '123123123',
            'role' => UserRoleEnum::Admin->value,
        ]);
    }

    private function createUsers(): void
    {
        User::factory($this->userCount)->create();
    }
}
