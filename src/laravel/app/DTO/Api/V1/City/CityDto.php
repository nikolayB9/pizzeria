<?php

namespace App\DTO\Api\V1\City;

use App\Models\City;
use Illuminate\Support\Collection;

class CityDto
{
    public function __construct(
        public int    $id,
        public string $name,
    )
    {
    }

    /**
     * Создает DTO из модели City.
     *
     * @param City $city Экземпляр модели City.
     *
     * @return self
     */
    public static function fromModel(City $city): self
    {
        return new self(
            id: $city->id,
            name: $city->name,
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection $cities Коллекция моделей City.
     *
     * @return CityDto[] Массив DTO.
     */
    public static function collection(Collection $cities): array
    {
        return $cities->map(fn($city) => self::fromModel($city))->toArray();
    }
}
