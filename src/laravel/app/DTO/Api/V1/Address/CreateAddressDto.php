<?php

namespace App\DTO\Api\V1\Address;

class CreateAddressDto
{
    public function __construct(
        public int     $user_id,
        public int     $city_id,
        public int     $street_id,
        public string  $house,
        public ?string $entrance,
        public ?string $floor,
        public ?string $flat,
        public ?string $intercom_code,
    )
    {
    }

    /**
     * Преобразует DTO в массив для вставки в БД.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'city_id' => $this->city_id,
            'street_id' => $this->street_id,
            'house' => $this->house,
            'entrance' => $this->entrance,
            'floor' => $this->floor,
            'flat' => $this->flat,
            'intercom_code' => $this->intercom_code,
        ];
    }
}
