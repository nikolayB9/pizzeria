<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Address\AddressDto;
use App\DTO\Api\V1\Address\AddressShortDto;
use App\DTO\Api\V1\Address\CreateAddressDto;
use App\Exceptions\Address\FailedSetDefaultAddressException;
use App\Exceptions\Address\UserAddressNotAddException;
use App\Exceptions\Address\UserAddressNotFoundException;
use App\Repositories\Address\AddressRepositoryInterface;
use App\Services\Traits\AuthenticatedUserTrait;

class AddressService
{
    use AuthenticatedUserTrait;

    public function __construct(private readonly AddressRepositoryInterface $addressRepository)
    {
    }

    /**
     * Возвращает все адреса авторизованного пользователя.
     *
     * @return AddressShortDto[] Массив DTO с сокращённой информацией об адресах (город, улица, дом).
     */
    public function getUserAddresses(): array
    {
        $userId = $this->userIdOrFail();
        $userAddresses = $this->addressRepository->getAddressesByUserId($userId);

        return AddressShortDto::collection($userAddresses);
    }

    /**
     * Возвращает адрес по его ID для авторизованного пользователя.
     *
     * @param int $addressId ID адреса пользователя.
     *
     * @return AddressDto DTO с полными данными адреса.
     * @throws UserAddressNotFoundException Если адрес не найден.
     */
    public function getUserAddress(int $addressId): AddressDto
    {
        $userId = $this->userIdOrFail();
        $address = $this->addressRepository->getUserAddressById($userId, $addressId);

        return AddressDto::fromModel($address);
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
        $userId = $this->userIdOrFail();
        $newAddress = $this->addressRepository->createAddressForUser($userId, $addressDto);

        return AddressShortDto::fromModel($newAddress);
    }

    /**
     * Устанавливает адрес пользователя по умолчанию (is_default = true).
     *
     * @param int $addressId ID адреса.
     *
     * @return void
     * @throws FailedSetDefaultAddressException Если не удалось установить адрес по умолчанию.
     */
    public function setDefaultUserAddress(int $addressId): void
    {
        $userId = $this->userIdOrFail();

        $this->addressRepository->setDefaultUserAddressById($userId, $addressId);
    }
}
