<?php

namespace Database\Seeders;

use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->createAdmin();
        $this->createUser();

        $count = config('seeder.count_users');

        if ($count > 0) {
            User::factory($count)->create();
        }
    }

    private function createAdmin(): void
    {
        $name = config('seeder.admin.name');
        $email = config('seeder.admin.email');
        $password = config('seeder.admin.password');

        $exists = User::where('email', $email)
            ->where('role', UserRoleEnum::Admin->value)
            ->exists();

        if (!$exists) {
            User::factory()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => UserRoleEnum::Admin,
            ]);
        }
    }

    private function createUser(): void
    {
        $name = config('seeder.user.name');
        $email = config('seeder.user.email');
        $password = config('seeder.user.password');

        $exists = User::where('email', $email)
            ->where('role', UserRoleEnum::User->value)
            ->exists();

        if (!$exists) {
            User::factory()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => UserRoleEnum::User,
            ]);
        }
    }
}
