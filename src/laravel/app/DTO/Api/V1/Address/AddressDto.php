<?php

namespace App\DTO\Api\V1\Address;

use App\Models\Address;

class AddressDto
{
    public function __construct(
        public int     $id,
        public int     $city_id,
        public int     $street_id,
        public string  $house,
        public ?string $entrance,
        public ?string $floor,
        public ?string $flat,
        public ?string $intercom_code,
        public bool    $is_default,
    )
    {
    }

    /**
     * Создает DTO из модели Address.
     *
     * @param Address $address Экземпляр модели Address со всеми полями.
     *
     * @return self
     */
    public static function fromModel(Address $address): self
    {
        return new self(
            id: $address->id,
            city_id: $address->city_id,
            street_id: $address->street_id,
            house: $address->house,
            entrance: $address->entrance,
            floor: $address->floor,
            flat: $address->flat,
            intercom_code: $address->intercom_code,
            is_default: $address->is_default,
        );
    }
}
