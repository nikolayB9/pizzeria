<?php

namespace Database\Seeders;

use App\Enums\User\UserRoleEnum;
use App\Models\Address;
use App\Models\City;
use App\Models\Street;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Создает дефолтный адрес для каждого пользователя (кроме администраторов).
     *
     * @return void
     */
    public function run(): void
    {
        $cities = City::all();
        $cityIds = $cities->pluck('id')->values();

        $streets = Street::whereIn('city_id', $cityIds)->get();

        $users = User::where('role', UserRoleEnum::User->value)->get();

        foreach ($users as $user) {
            $street = $streets->random();

            Address::factory()->create([
                'user_id' => $user->id,
                'city_id' => $street->city_id,
                'street_id' => $street->id,
                'is_default' => true,
            ]);
        }
    }
}
