<?php

namespace App\Repositories\Address;

use App\DTO\Api\V1\Address\CreateAddressDto;
use App\Exceptions\Address\UserAddressNotAddException;
use App\Models\Address;

interface AddressRepositoryInterface
{
    /**
     * Создает и возвращает адрес пользователя.
     *
     * @param CreateAddressDto $dto
     *
     * @return Address
     * @throws UserAddressNotAddException
     */
    public function createAddress(CreateAddressDto $dto): Address;
}
