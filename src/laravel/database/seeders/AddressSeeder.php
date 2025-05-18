<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $city = City::where('name', 'Киров')->firstOrFail();
        $street = $city->streets()->firstOrFail();
        $user = User::where('email', 'user@mail.ru')->firstOrFail();

        $addressData = [
            'user_id' => $user->id,
            'city_id' => $city->id,
            'street_id' => $street->id,
            'house' => '135',
            'entrance' => '3',
            'floor' => '5',
            'flat' => '72',
            'intercom_code' => '72',
        ];

        if (!Address::where($addressData)->exists()) {
            Address::create($addressData);
        }
    }
}
