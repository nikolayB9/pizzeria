<?php

namespace App\Repositories\Address;

use App\DTO\Api\V1\Address\CreateAddressDto;
use App\Exceptions\Address\FailedSetDefaultAddressException;
use App\Exceptions\Address\UserAddressNotAddException;
use App\Exceptions\Address\UserAddressNotFoundException;
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
     * Создает и возвращает адрес пользователя.
     *
     * @param int $userId
     * @param CreateAddressDto $dto
     *
     * @return Address
     * @throws UserAddressNotAddException
     */
    public function createAddressForUser(int $userId, CreateAddressDto $dto): Address;

    /**
     * Устанавливает адрес пользователя как основной (is_default = true).
     *
     * @param int $userId
     * @param int $addressId
     *
     * @return void
     *
     * @throws FailedSetDefaultAddressException
     */
    public function setDefaultUserAddressById(int $userId, int $addressId): void;
}
