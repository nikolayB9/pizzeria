<?php

namespace App\Repositories\User;

use App\Exceptions\User\MissingUserException;
use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Получает модель пользователя по ID с минимальным набором полей для дальнейшего формирования превью.
     *
     * @param int $userId
     *
     * @return User
     * @throws MissingUserException
     */
    public function getPreviewModelById(int $userId): User;

    /**
     * Получает модель пользователя по ID с минимальным набором данных для оформления заказа.
     *
     * @param int $userId
     *
     * @return User
     * @throws MissingUserException
     */
    public function getCheckoutDataById(int $userId): User;
}
