<?php

namespace App\Repositories\Profile;

use App\DTO\Api\V1\Checkout\CheckoutUserDataDto;
use App\DTO\Api\V1\Profile\ProfileDto;
use App\DTO\Api\V1\Profile\ProfilePreviewDto;
use App\Exceptions\User\MissingDefaultUserAddressException;
use App\Exceptions\User\MissingUserException;

interface ProfileRepositoryInterface
{
    /**
     * Возвращает данные пользователя по его ID.
     *
     * @param int $userId
     *
     * @return ProfileDto
     * @throws MissingUserException
     */
    public function getProfileById(int $userId): ProfileDto;

    /**
     * Получает данные пользователя по его ID с минимальным набором полей для формирования превью.
     *
     * @param int $userId
     *
     * @return ProfilePreviewDto
     * @throws MissingUserException
     */
    public function getPreviewModelById(int $userId): ProfilePreviewDto;

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
