<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        if (!City::where('name', 'Киров')->exists()) {
            City::create([
                'name' => 'Киров',
            ]);
        }
    }
}
