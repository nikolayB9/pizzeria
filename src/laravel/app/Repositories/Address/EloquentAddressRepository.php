<?php

namespace App\Repositories\Address;

use App\DTO\Api\V1\Address\CreateAddressDto;
use App\Exceptions\Address\FailedSetDefaultAddressException;
use App\Exceptions\Address\UserAddressNotAddException;
use App\Models\Address;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentAddressRepository implements AddressRepositoryInterface
{
    /**
     * Возвращает список адресов пользователя в сокращённом виде (город, улица, дом).
     *
     * @param int $id Идентификатор пользователя.
     *
     * @return Collection Коллекция моделей Address с минимальным набором данных.
     */
    public function getAddressesByUserId(int $id): Collection
    {
        return Address::where('user_id', $id)
            ->select('id', 'user_id', 'is_default', 'city_id', 'street_id', 'house')
            ->orderBy('is_default', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Создает и возвращает адрес пользователя.
     *
     * @param int $userId Идентификатор пользователя.
     * @param CreateAddressDto $dto DTO с данными для создания адреса.
     *
     * @return Address Модель созданного адреса с предзагруженными отношениями city и street.
     * @throws UserAddressNotAddException Если произошла ошибка при создании адреса.
     */
    public function createAddressForUser(int $userId, CreateAddressDto $dto): Address
    {
        try {
            return DB::transaction(function () use ($userId, $dto) {
                $address = Address::create($dto->toArray());
                $this->setDefaultUserAddressInternal($userId, $address->id);
                $address->load(['city', 'street']);
                return $address;
            });
        } catch (\Throwable $e) {
            Log::error('Ошибка при добавлении нового адреса пользователя.', [
                'exception' => $e,
                'user_id' => auth()->id(),
                'create_data_dto' => $dto,
                'method' => __METHOD__,
            ]);

            throw new UserAddressNotAddException(
                'Не удалось добавить новый адрес. Пожалуйста, попробуйте снова.'
            );
        }
    }

    /**
     * Устанавливает адрес пользователя как основной (is_default = true).
     *
     * @param int $userId ID пользователя.
     * @param int $addressId ID адреса, который нужно сделать основным.
     *
     * @return void
     *
     * @throws FailedSetDefaultAddressException При ошибке во время установки.
     */
    public function setDefaultUserAddressById(int $userId, int $addressId): void
    {
        try {
            DB::transaction(function () use ($userId, $addressId) {
                $this->setDefaultUserAddressInternal($userId, $addressId);
            });
        } catch (\Throwable $e) {
            Log::error('Ошибка при установке адреса по умолчанию.', [
                'exception' => $e,
                'user_id' => $userId,
                'address_id' => $addressId,
                'method' => __METHOD__,
            ]);

            throw new FailedSetDefaultAddressException('Не удалось установить адрес по умолчанию.');
        }
    }

    /**
     * Устанавливает указанный адрес пользователя как адрес по умолчанию.
     *
     * @param int $userId ID пользователя.
     * @param int $addressId ID адреса, который нужно сделать основным (is_default = true).
     *
     * @return void
     * @throws ModelNotFoundException Если адрес пользователя не найден.
     */
    private function setDefaultUserAddressInternal(int $userId, int $addressId): void
    {
        $newDefaultAddress = Address::where('id', $addressId)->where('user_id', $userId)->firstOrFail();

        Address::where('user_id', $userId)
            ->whereNot('id', $addressId)
            ->update(['is_default' => false]);

        $newDefaultAddress->update(['is_default' => true]);
    }
}
