<?php

namespace App\Repositories\City;

use App\Exceptions\City\CityNotFoundException;
use App\Models\City;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EloquentCityRepository implements CityRepositoryInterface
{
    /**
     * Возвращает коллекцию со всеми городами.
     *
     * @return Collection Коллекция городов.
     */
    public function getAll(): Collection
    {
        return City::all();
    }

    /**
     * Возвращает все улицы по указанному идентификатору города.
     *
     * @param int $cityId ID города.
     *
     * @return Collection Коллекция моделей Street для указанного города.
     * @throws CityNotFoundException Если город с переданным ID не найден.
     */
    public function getCityStreetsById(int $cityId): Collection
    {
        try {
            $city = City::where('id', $cityId)->firstOrFail();
        } catch (ModelNotFoundException) {
            Log::warning("Не найден город с ID [$cityId] при попытке загрузить его улицы.", [
                'city_id' => $cityId,
                'user_id' => auth()->id(),
                'method' => __METHOD__
            ]);

            throw new CityNotFoundException('Город не найден');
        }

        return $city->streets;
    }
}
