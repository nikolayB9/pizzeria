<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Checkout\CheckoutUserDataDto;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Traits\AuthenticatedUserTrait;

class CheckoutService
{
    use AuthenticatedUserTrait;

    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    /**
     * Возвращает данные пользователя для оформления заказа.
     *
     * @return CheckoutUserDataDto DTO с данными пользователя, необходимыми для оформления заказа.
     */
    public function getCheckoutUserData(): CheckoutUserDataDto
    {
        $userId = $this->userIdOrFail();
        $userData = $this->userRepository->getCheckoutDataById($userId);

        return CheckoutUserDataDto::fromModel($userData);
    }
}
