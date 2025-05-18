<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Address\AddressShortDto;
use App\DTO\Api\V1\Address\CreateAddressDto;
use App\Exceptions\Address\UserAddressNotAddException;
use App\Repositories\Address\AddressRepositoryInterface;

class AddressService
{
    public function __construct(private readonly AddressRepositoryInterface $addressRepository)
    {
    }

    /**
     * Создает новый адрес пользователя.
     *
     * @param CreateAddressDto $addressDto DTO с данными для создания адреса.
     *
     * @return AddressShortDto DTO для отображения созданного адреса в списке.
     * @throws UserAddressNotAddException Если произошла ошибка при создании адреса.
     */
    public function createUserAddress(CreateAddressDto $addressDto): AddressShortDto
    {
        $newAddress = $this->addressRepository->createAddress($addressDto);

        return AddressShortDto::fromModel($newAddress);
    }
}
