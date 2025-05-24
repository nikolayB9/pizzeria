<?php

namespace Tests\Helpers;

use App\Models\City;
use App\Models\Street;
use Illuminate\Support\Collection;

class CityHelper
{
    /**
     * Создает один или несколько городов.
     *
     * @param int $count Количество создаваемых городов.
     *
     * @return City|Collection Модель или коллекция созданных городов.
     */
    public static function createCities(int $count = 1): City|Collection
    {
        $cities = City::factory($count)->create();

        return $count === 1 ? $cities->first() : $cities;
    }

    /**
     * Создает улицы для переданного города.
     *
     * @param int $cityId ID города.
     * @param int $count Количество создаваемых улиц.
     *
     * @return Street|Collection Модель или коллекция созданных улиц.
     */
    public static function createStreetsForCity(int $cityId, int $count): Street|Collection
    {
        $streets = Street::factory($count)->create([
            'city_id' => $cityId,
        ]);

        return $count === 1 ? $streets->first() : $streets;
    }
}
