<?php

namespace App\Repositories\User;

use App\DTO\Api\V1\Checkout\CheckoutUserDataDto;
use App\Exceptions\User\MissingDefaultUserAddressException;
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
     * Возвращает DTO с минимальным набором данных для оформления заказа, включая дефолтный адрес.
     *
     * @param int $userId
     *
     * @return CheckoutUserDataDto
     * @throws MissingUserException
     */
    public function getCheckoutInfo(int $userId): CheckoutUserDataDto;

    /**
     * Возвращает ID дефолтного адреса доставки для пользователя или выбрасывает исключение, если адрес не найден.
     *
     * @param int $userId
     *
     * @return int
     * @throws MissingUserException
     * @throws MissingDefaultUserAddressException
     */
    public function getDefaultAddressIdOrThrow(int $userId): int;
}
