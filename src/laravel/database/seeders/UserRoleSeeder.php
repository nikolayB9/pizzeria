<?php

namespace Database\Seeders;

use App\Enums\User\UserRoleEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (UserRoleEnum::cases() as $role) {
            DB::table('user_roles')
                ->updateOrInsert(
                  ['id' => $role->value],
                  ['slug' => $role->slug(), 'label' => $role->label()],
                );
        }
    }
}
