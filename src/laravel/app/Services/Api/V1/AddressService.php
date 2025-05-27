<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Address\AddressDto;
use App\DTO\Api\V1\Address\AddressShortDto;
use App\DTO\Api\V1\Address\CreateAddressDto;
use App\DTO\Api\V1\Address\UpdateAddressDto;
use App\Exceptions\Address\FailedSetDefaultAddressException;
use App\Exceptions\Address\UserAddressNotAddException;
use App\Exceptions\Address\UserAddressNotDeletedException;
use App\Exceptions\Address\UserAddressNotFoundException;
use App\Exceptions\Address\UserAddressNotUpdatedException;
use App\Repositories\Api\V1\Address\AddressRepositoryInterface;
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
     * @return void
     * @throws UserAddressNotAddException Если произошла ошибка при создании адреса.
     */
    public function createUserAddress(CreateAddressDto $addressDto): void
    {
        $userId = $this->userIdOrFail();
        $this->addressRepository->createAddressForUser($userId, $addressDto);
    }

    /**
     * Редактирует данные адреса пользователя.
     *
     * @param int $addressId ID редактируемого адреса.
     * @param UpdateAddressDto $addressDto DTO с данными для редактирования.
     *
     * @return void
     * @throws UserAddressNotFoundException Если адрес не найден.
     * @throws UserAddressNotUpdatedException Если произошла ошибка при редактировании адреса.
     */
    public function updateUserAddress(int $addressId, UpdateAddressDto $addressDto): void
    {
        $userId = $this->userIdOrFail();
        $this->addressRepository->updateUserAddressFromDto($userId, $addressId, $addressDto);
    }

    /**
     * Устанавливает адрес пользователя по умолчанию (is_default = true).
     *
     * @param int $addressId ID адреса.
     *
     * @return void
     * @throws UserAddressNotFoundException Если адрес не найден.
     * @throws FailedSetDefaultAddressException Если не удалось установить адрес по умолчанию.
     */
    public function setDefaultUserAddress(int $addressId): void
    {
        $userId = $this->userIdOrFail();

        $this->addressRepository->setDefaultUserAddressById($userId, $addressId);
    }

    /**
     * Удаляет адрес текущего авторизованного пользователя.
     *
     * Если адрес используется в заказах, он не удаляется, а отвязывается от пользователя.
     *
     * @param int $addressId ID удаляемого адреса.
     *
     * @return void
     * @throws UserAddressNotFoundException Если адрес не найден.
     * @throws UserAddressNotDeletedException Если произошла ошибка при удалении адреса.
     */
    public function deleteUserAddress(int $addressId): void
    {
        $userId = $this->userIdOrFail();
        $this->addressRepository->deleteUserAddressById($userId, $addressId);
    }
}
