<?php

namespace App\Repositories\Api\V1\Address;

use App\DTO\Api\V1\Address\CreateAddressDto;
use App\DTO\Api\V1\Address\UpdateAddressDto;
use App\Exceptions\Address\FailedSetDefaultAddressException;
use App\Exceptions\Address\UserAddressNotAddException;
use App\Exceptions\Address\UserAddressNotDeletedException;
use App\Exceptions\Address\UserAddressNotFoundException;
use App\Exceptions\Address\UserAddressNotUpdatedException;
use App\Models\Address;
use Illuminate\Support\Collection;

interface AddressRepositoryInterface
{
    /**
     * Возвращает список адресов пользователя в сокращённом виде (город, улица, дом).
     *
     * @param int $id
     *
     * @return Collection
     */
    public function getAddressesByUserId(int $id): Collection;

    /**
     * Возвращает адрес пользователя по ID и user_id.
     *
     * @param int $userId
     * @param int $addressId
     *
     * @return Address
     * @throws UserAddressNotFoundException
     */
    public function getUserAddressById(int $userId, int $addressId): Address;

    /**
     * Создает новый адрес пользователя.
     *
     * @param int $userId
     * @param CreateAddressDto $dto
     *
     * @return void
     * @throws UserAddressNotAddException
     */
    public function createAddressForUser(int $userId, CreateAddressDto $dto): void;

    /**
     * Редактирует данные адреса пользователя.
     *
     * @param int $userId
     * @param int $addressId
     * @param UpdateAddressDto $dto
     *
     * @return void
     * @throws UserAddressNotFoundException
     * @throws UserAddressNotUpdatedException
     */
    public function updateUserAddressFromDto(int $userId, int $addressId, UpdateAddressDto $dto): void;

    /**
     * Устанавливает адрес пользователя как основной (is_default = true).
     *
     * @param int $userId
     * @param int $addressId
     *
     * @return void
     * @throws UserAddressNotFoundException
     * @throws FailedSetDefaultAddressException
     */
    public function setDefaultUserAddressById(int $userId, int $addressId): void;

    /**
     * Удаляет адрес, если он не связан с заказами. Иначе — отвязывает его от пользователя.
     *
     * @param int $userId
     * @param int $addressId
     *
     * @return void
     * @throws UserAddressNotFoundException
     * @throws UserAddressNotDeletedException
     */
    public function deleteUserAddressById(int $userId, int $addressId): void;
}
