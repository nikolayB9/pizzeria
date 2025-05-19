<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\City\CityDto;
use App\DTO\Api\V1\City\StreetDto;
use App\Exceptions\City\CityNotFoundException;
use App\Repositories\City\CityRepositoryInterface;
use App\Services\Traits\AuthenticatedUserTrait;

class CityService
{
    use AuthenticatedUserTrait;

    public function __construct(private readonly CityRepositoryInterface $cityRepository)
    {
    }

    /**
     * Возвращает все города.
     *
     * @return CityDto[] Массив DTO городов.
     */
    public function getAllCities(): array
    {
        $cities = $this->cityRepository->getAll();

        return CityDto::collection($cities);
    }

    /**
     * Возвращает все улицы города.
     *
     * @param int $cityId ID города.
     *
     * @return StreetDto[] Массив DTO улиц.
     * @throws CityNotFoundException Если не найден город, для которого запрашиваются улицы.
     */
    public function getCityStreets(int $cityId): array
    {
        $streets = $this->cityRepository->getCityStreetsById($cityId);

        return StreetDto::collection($streets);
    }
}
