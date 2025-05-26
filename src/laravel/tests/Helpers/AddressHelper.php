<?php

namespace Tests\Helpers;

use App\Models\Address;
use Illuminate\Support\Collection;

class AddressHelper
{
    /**
     * Создаёт один или несколько адресов для заданного пользователя.
     *
     * @param int $userId ID пользователя.
     * @param int $count Количество создаваемых адресов.
     * @param int $countCities Количество создаваемых городов.
     *                         Один случайно выбранный город используется для всех адресов.
     * @param int $countStreets Количество улиц, создаваемых для каждого города.
     *                          Для каждого адреса выбирается случайная улица этого города.
     * @param bool $isDefault Если true — один случайный адрес будет отмечен как дефолтный.
     *
     * @return Address|Collection<Address> Один адрес или коллекция адресов.
     */
    public static function createAddresses(int  $userId,
                                           int  $count = 1,
                                           int  $countCities = 1,
                                           int  $countStreets = 1,
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
