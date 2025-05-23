<?php

namespace App\Repositories\Profile;

use App\DTO\Api\V1\Checkout\CheckoutUserDataDto;
use App\DTO\Api\V1\Profile\ProfileDto;
use App\Exceptions\User\MissingDefaultUserAddressException;
use App\Exceptions\User\MissingUserException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentProfileRepository implements ProfileRepositoryInterface
{
    /**
     * Возвращает данные пользователя по его ID.
     *
     * @param int $userId ID пользователя.
     *
     * @return ProfileDto Данные профиля пользователя.
     * @throws UserNotFoundException Если пользователь не найден.
     */
    public function getProfileById(int $userId): ProfileDto
    {
        try {
            return ProfileDto::fromModel(
                User::where('id', $userId)->firstOrFail()
            );
        } catch (ModelNotFoundException $e) {
            throw new UserNotFoundException('Профиль не найден.');
        }
    }

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
     * Возвращает DTO с минимальным набором данных для оформления заказа, включая дефолтный адрес.
     *
     * @param int $userId Идентификатор пользователя.
     *
     * @return CheckoutUserDataDto Данные пользователя с дефолтным адресом.
     * @throws MissingUserException Если пользователь не найден (Runtime: ожидается существующий $userId).
     */
    public function getCheckoutInfo(int $userId): CheckoutUserDataDto
    {
        try {
            $user = User::where('id', $userId)
                ->select('id', 'name', 'email', 'phone_number')
                ->with([
                    'defaultAddress:id,user_id,is_default,city_id,street_id,house',
                ])
                ->firstOrFail();

            return CheckoutUserDataDto::fromModel($user);
        } catch (ModelNotFoundException) {
            throw new MissingUserException("Пользователь с ID [$userId] не найден.");
        }
    }


    /**
     * Возвращает ID дефолтного адреса доставки для пользователя или выбрасывает исключение, если адрес не найден.
     *
     * @param int $userId ID пользователя.
     *
     * @return int ID адреса пользователя, используемого по умолчанию для доставки заказов.
     * @throws MissingUserException Если пользователь не найден (Runtime: ожидается существующий $userId).
     * @throws MissingDefaultUserAddressException Если дефолтный адрес не найден.
     */
    public function getDefaultAddressIdOrThrow(int $userId): int
    {
        try {
            $user = User::where('id', $userId)
                ->select('id')
                ->with('defaultAddress:id,user_id')
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            throw new MissingUserException("Пользователь с ID [$userId] не найден.");
        }

        if (!$user->defaultAddress) {
            throw new MissingDefaultUserAddressException('Не найден дефолтный адрес пользователя.');
        }

        return $user->defaultAddress->id;
    }
}
