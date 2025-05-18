<?php

namespace App\DTO\Api\V1\Checkout;

use App\DTO\Api\V1\Address\AddressShortDto;
use App\DTO\Traits\RequiresPreload;
use App\Models\User;

class CheckoutUserDataDto
{
    use RequiresPreload;

    public function __construct(
        public string           $name,
        public string           $email,
        public string           $phone_number,
        public ?AddressShortDto $address,
    )
    {
    }

    /**
     * Создает DTO из модели User.
     *
     * @param User $user Модель User с предзагруженными отношениями latestOrder.address, latestAddress.
     *
     * @return self
     */
    public static function fromModel(User $user): self
    {
        self::checkRequireAllRelationPaths($user, ['latestOrder.address', 'latestAddress']);

        $addressModel = $user->latestOrder?->address ?? $user->latestAddress;

        return new self(
            name: $user->name,
            email: $user->email,
            phone_number: $user->phone_number,
            address: $addressModel ? AddressShortDto::fromModel($addressModel) : null,
        );
    }
}
