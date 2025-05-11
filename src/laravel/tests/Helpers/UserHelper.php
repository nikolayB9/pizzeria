<?php

namespace Tests\Helpers;

use App\Models\User;
use Database\Seeders\UserRoleSeeder;

class UserHelper
{
    public static function createUser(): User
    {
        (new UserRoleSeeder())->run();

        return User::factory()->create();
    }
}
