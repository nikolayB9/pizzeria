<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Checkout\CheckoutUserDataDto;
use App\Exceptions\User\AuthenticatedUserExpectedException;
use App\Repositories\User\UserRepositoryInterface;

class CheckoutService
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    /**
     * Возвращает данные пользователя для оформления заказа.
     *
     * @return CheckoutUserDataDto DTO с данными пользователя, необходимыми для оформления заказа.
     * @throws AuthenticatedUserExpectedException Если пользователь не авторизован.
     *         (Это Runtime исключение, так как авторизация должна быть проверена middleware).
     */
    public function getCheckoutUserData(): CheckoutUserDataDto
    {
        if (!auth()->check()) {
            throw new AuthenticatedUserExpectedException('Пользователь должен быть авторизован.');
        }

        $userData = $this->userRepository->getCheckoutDataById(auth()->id());

        return CheckoutUserDataDto::fromModel($userData);
    }
}
