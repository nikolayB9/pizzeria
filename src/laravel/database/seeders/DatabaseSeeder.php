<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserRoleSeeder::class,
            UserSeeder::class,
            CategoryTypeSeeder::class,
            CategorySeeder::class,
            ParameterGroupSeeder::class,
            ParameterSeeder::class,
            CategoryParameterSeeder::class,
            ProductImageTypeSeeder::class,
        ]);
    }
}
