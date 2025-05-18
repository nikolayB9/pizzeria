<?php

namespace App\Repositories\Address;

use App\DTO\Api\V1\Address\CreateAddressDto;
use App\Exceptions\Address\UserAddressNotAddException;
use App\Models\Address;
use Illuminate\Support\Facades\Log;

class EloquentAddressRepository implements AddressRepositoryInterface
{
    /**
     * Создает и возвращает адрес пользователя.
     *
     * @param CreateAddressDto $dto DTO с данными для создания адреса.
     *
     * @return Address Модель созданного адреса с предзагруженными отношениями city и street.
     * @throws UserAddressNotAddException Если произошла ошибка при создании адреса.
     */
    public function createAddress(CreateAddressDto $dto): Address
    {
        try {
            $address = Address::create($dto->toArray());
            $address->load(['city', 'street']);

            return $address;
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
}
