<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\City;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $city = City::where('name', 'Киров')->firstOrFail();
        $street = $city->streets()->firstOrFail();

        $addressData = [
            'city' => $city->id,
            'street' => $street->id,
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
