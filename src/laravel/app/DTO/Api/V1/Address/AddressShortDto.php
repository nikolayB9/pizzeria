<?php

namespace App\DTO\Api\V1\Address;

use App\DTO\Traits\RequiresPreload;
use App\Models\Address;
use Illuminate\Support\Collection;

class AddressShortDto
{
    use RequiresPreload;

    public function __construct(
        public int    $id,
        public string $city,
        public string $street,
        public string $house,
        public bool   $is_default,
    )
    {
    }

    /**
     * Создает DTO из модели Address.
     *
     * @param Address $address Экземпляр модели Address с предзагруженными отношениями city и street.
     *
     * @return self
     */
    public static function fromModel(Address $address): self
    {
        self::checkRequireNotNullRelations($address, ['city', 'street']);

        return new self(
            id: $address->id,
            city: $address->city->name,
            street: $address->street->name,
            house: $address->house,
            is_default: $address->is_default,
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection<int, Address> $addresses Коллекция моделей Address.
     *
     * @return AddressShortDto[] Массив DTO.
     */
    public static function collection(Collection $addresses): array
    {
        return $addresses->map(fn($address) => self::fromModel($address))->toArray();
    }
}
