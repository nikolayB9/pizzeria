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
use App\Models\User;
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
     * Возвращает адрес пользователя по ID и user_id.
     *
     * @param int $userId ID пользователя, к которому относится адрес.
     * @param int $addressId ID адреса.
     *
     * @return Address Модель Address со всеми полями.
     * @throws UserAddressNotFoundException Если адрес пользователя не найден.
     */
    public function getUserAddressById(int $userId, int $addressId): Address
    {
        return $this->getUserAddressOrFail($userId, $addressId);
    }

    /**
     * Создает новый адрес пользователя.
     *
     * @param int $userId Идентификатор пользователя.
     * @param CreateAddressDto $dto DTO с данными для создания адреса.
     *
     * @return void
     * @throws UserAddressNotAddException Если произошла ошибка при создании адреса.
     */
    public function createAddressForUser(int $userId, CreateAddressDto $dto): void
    {
        try {
            DB::transaction(function () use ($userId, $dto) {
                $address = Address::create($dto->toArray());
                $this->setDefaultUserAddressInternal($userId, $address);
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
     * Редактирует данные адреса пользователя.
     *
     * @param int $userId ID пользователя, которому принадлежит адрес.
     * @param int $addressId ID изменяемого адреса.
     * @param UpdateAddressDto $dto DTO с данными для редактирования.
     *
     * @return void
     * @throws UserAddressNotFoundException Если адрес пользователя не найден.
     * @throws UserAddressNotUpdatedException Если произошла ошибка при редактировании данных адреса.
     */
    public function updateUserAddressFromDto(int $userId, int $addressId, UpdateAddressDto $dto): void
    {
        $address = $this->getUserAddressOrFail($userId, $addressId);

        try {
            $address->update($dto->toArray());
        } catch (\Throwable $e) {
            Log::error('Ошибка при изменении данных адреса.', [
                'exception' => $e,
                'user_id' => $userId,
                'address_id' => $addressId,
                'dto' => $dto,
                'method' => __METHOD__,
            ]);

            throw new UserAddressNotUpdatedException(
                'Не удалось изменить данные адреса. Пожалуйста, попробуйте снова.'
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
     * @throws UserAddressNotFoundException Если адрес не найден.
     * @throws FailedSetDefaultAddressException При ошибке во время установки.
     */
    public function setDefaultUserAddressById(int $userId, int $addressId): void
    {
        $address = $this->getUserAddressOrFail($userId, $addressId);

        try {
            DB::transaction(function () use ($userId, $address) {
                $this->setDefaultUserAddressInternal($userId, $address);
            });
        } catch (\Throwable $e) {
            Log::error('Ошибка при установке адреса по умолчанию.', [
                'user_id' => $userId,
                'address_id' => $addressId,
                'method' => __METHOD__,
                'exception' => $e->getMessage(),
            ]);

            throw new FailedSetDefaultAddressException('Не удалось установить адрес по умолчанию.');
        }
    }

    /**
     * Удаляет адрес, если он не связан с заказами. Иначе — отвязывает его от пользователя, установив user_id = null.
     *
     * После удаления или отвязки, если у пользователя остались адреса и ни один из них не является дефолтным,
     * автоматически назначается последний добавленный адрес как дефолтный.
     *
     * @param int $userId ID пользователя.
     * @param int $addressId ID удаляемого адреса.
     *
     * @return void
     * @throws UserAddressNotFoundException Если адрес не найден или не принадлежит пользователю.
     * @throws UserAddressNotDeletedException Если произошла ошибка при удалении или обновлении адреса.
     */
    public function deleteUserAddressById(int $userId, int $addressId): void
    {
        $address = $this->getUserAddressOrFail($userId, $addressId);

        try {
            DB::transaction(function () use ($userId, $address) {
                if ($address->orders()->exists()) {
                    $address->update([
                        'user_id' => null,
                        'is_default' => false,
                    ]);
                } else {
                    $address->delete();
                }

                $user = User::find($userId);

                if (!$user->defaultAddress()->exists() && $user->latestAddress()->exists()) {
                    $this->setDefaultUserAddressInternal($userId, $user->latestAddress);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Ошибка при удалении адреса.', [
                'exception' => $e,
                'user_id' => $userId,
                'address_id' => $addressId,
                'method' => __METHOD__,
            ]);

            throw new UserAddressNotDeletedException(
                'Не удалось удалить адрес. Пожалуйста, попробуйте снова.'
            );
        }
    }

    /**
     * Проверяет существование адреса пользователя и возвращает его.
     *
     * @param int $userId ID пользователя.
     * @param int $addressId ID адреса пользователя.
     *
     * @return Address Модель Address со всеми полями.
     * @throws UserAddressNotFoundException Если адрес не найден.
     */
    private function getUserAddressOrFail(int $userId, int $addressId): Address
    {
        try {
            return Address::where('id', $addressId)
                ->where('user_id', $userId)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            Log::warning('Не удалось найти адрес для пользователя', [
                'user_id' => $userId,
                'address_id' => $addressId,
                'method' => __METHOD__,
                'exception' => $e->getMessage(),
            ]);

            throw new UserAddressNotFoundException('Адрес не найден.');
        }
    }

    /**
     * Устанавливает указанный адрес пользователя как адрес по умолчанию.
     *
     * @param int $userId ID пользователя.
     * @param Address $address Модель адреса, который нужно сделать основным (is_default = true).
     *
     * @return void
     */
    private function setDefaultUserAddressInternal(int $userId, Address $address): void
    {
        Address::where('user_id', $userId)
            ->whereNot('id', $address->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);
    }
}
