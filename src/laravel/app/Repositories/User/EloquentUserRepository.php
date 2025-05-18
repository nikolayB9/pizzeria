<?php

namespace App\Repositories\User;

use App\Exceptions\User\MissingUserException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * Получает модель пользователя по ID с минимальным набором полей для формирования превью.
     *
     * @param int $userId ID пользователя.
     *
     * @return User Модель пользователя с ограниченным набором данных (для предпросмотра).
     * @throws MissingUserException Если пользователь не найден (Runtime: ожидается существующий $userId).
     */
    public function getPreviewModelById(int $userId): User
    {
        try {
            return User::where('id', $userId)
                ->select('id', 'name')
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            throw new MissingUserException("Пользователь с ID [$userId] не найден.");
        }
    }

    /**
     * Получает модель пользователя по ID с минимальным набором данных для оформления заказа.
     *
     * @param int $userId Идентификатор пользователя.
     *
     * @return User Модель пользователя с предзагруженными latestOrder (и его address) и latestAddress.
     * @throws MissingUserException Если пользователь не найден (Runtime: ожидается существующий $userId).
     */
    public function getCheckoutDataById(int $userId): User
    {
        try {
            return User::where('id', $userId)
                ->select('id', 'name', 'email', 'phone_number')
                ->with([
                    'latestOrder',
                    'latestOrder.address:id,city_id,street_id,house',
                    'latestAddress',
                ])
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            throw new MissingUserException("Пользователь с ID [$userId] не найден.");
        }
    }
}
