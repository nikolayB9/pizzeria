<?php

namespace Tests\Helpers;

use App\Models\Address;
use Illuminate\Support\Collection;

class AddressHelper
{

    public static function createAddresses(int  $userId,
                                           int  $count = 1,
                                           int  $countCities = 1,
                                           int  $countStreets = 3,
                                           bool $isDefault = true): Address|Collection
    {
        $cities = CityHelper::createCities($countCities);
        $collectionCities = collect()->wrap($cities);

        foreach ($collectionCities as $city) {
            CityHelper::createStreetsForCity($city->id, $countStreets);
        }

        $addresses = collect();
        $city = $collectionCities->random();

        while ($count > 0) {
            $street = $city->streets->random();

            $addresses->push(
                Address::factory()
                    ->create([
                        'user_id' => $userId,
                        'city_id' => $city->id,
                        'street_id' => $street->id,
                    ])
            );

            $count--;
        }

        if ($isDefault) {
            $address = $addresses->random();
            $address->update([
                'is_default' => true,
            ]);
        }

        return $addresses->count() === 1 ? $addresses->first() : $addresses;
    }
}
