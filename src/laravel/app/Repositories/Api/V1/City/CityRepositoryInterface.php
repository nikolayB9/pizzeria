<?php

namespace App\Repositories\Api\V1\City;

use App\Exceptions\City\CityNotFoundException;
use Illuminate\Support\Collection;

interface CityRepositoryInterface
{
    /**
     * Возвращает коллекцию со всеми городами.
     *
     * @return Collection Коллекция городов.
     */
    public function getAll(): Collection;


    /**
     * Возвращает все улицы по указанному идентификатору города.
     *
     * @param int $cityId
     *
     * @return Collection
     * @throws CityNotFoundException
     */
    public function getCityStreetsById(int $cityId): Collection;
}
