<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Checkout\CheckoutSummaryData;
use App\DTO\Api\V1\Checkout\CheckoutUserDataDto;
use App\Exceptions\Cart\CartIsEmptyException;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Traits\AuthenticatedUserTrait;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    use AuthenticatedUserTrait;

    public function __construct(private readonly UserRepositoryInterface $userRepository,
                                private readonly CartRepositoryInterface $cartRepository)
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

    /**
     * Возвращает данные стоимости товаров, стоимости доставки и общую сумму заказа для текущего пользователя.
     *
     * @return CheckoutSummaryData DTO с полями cartTotal (float), deliveryCost (float), total (float).
     * @throws CartIsEmptyException Если общая сумма в корзине равно 0.
     */
    public function getOrderSummary(): CheckoutSummaryData
    {
        $userId = $this->userIdOrFail();
        $cartTotal = $this->cartRepository->getTotalPriceByIdentifier('user_id', $userId);

        if ($cartTotal <= 0.0) {
            Log::warning('Общая стоимость корзины равна 0.0 при оформлении заказа', [
                'user_id' => $userId,
                'method' => __METHOD__,
            ]);
            throw new CartIsEmptyException('Корзина пуста.');
        }

        $deliveryCost = $cartTotal > 100 ? 0.0 : 100.0;

        $total = round($cartTotal + $deliveryCost, 2);

        return new CheckoutSummaryData($cartTotal, $deliveryCost, $total);
    }
}
