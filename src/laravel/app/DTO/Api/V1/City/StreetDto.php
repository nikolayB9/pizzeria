<?php

namespace App\DTO\Api\V1\City;

use App\Models\Street;
use Illuminate\Support\Collection;

class StreetDto
{
    public function __construct(
        public int    $id,
        public string $name,
    )
    {
    }

    /**
     * Создает DTO из модели Street.
     *
     * @param Street $street Экземпляр модели Street.
     *
     * @return self
     */
    public static function fromModel(Street $street): self
    {
        return new self(
            id: $street->id,
            name: $street->name,
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection $streets Коллекция моделей Street.
     *
     * @return StreetDto[] Массив DTO.
     */
    public static function collection(Collection $streets): array
    {
        return $streets->map(fn($street) => self::fromModel($street))->toArray();
    }
}
