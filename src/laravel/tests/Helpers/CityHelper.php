<?php

namespace Tests\Helpers;

use App\Models\City;
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
}
