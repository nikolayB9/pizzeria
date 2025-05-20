<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Cart\CartIsEmptyException;
use App\Http\Responses\ApiResponse;
use App\Services\Api\V1\CheckoutService;
use Illuminate\Http\JsonResponse;

class CheckoutController
{
    public function __construct(private readonly CheckoutService $checkoutService)
    {
    }

    /**
     * Возвращает данные авторизованного пользователя для оформления заказа.
     *
     * @return JsonResponse JSON-ответ с данными для оформления заказа.
     */
    public function userData(): JsonResponse
    {
        $checkoutData = $this->checkoutService->getCheckoutUserData();

        return ApiResponse::success(
            data: $checkoutData
        );
    }

    /**
     * Возвращает данные по стоимости товаров, доставке и общей сумме заказа.
     *
     * @return JsonResponse JSON с данными успешного ответа или ошибкой, если корзина пуста.
     */
    public function summaryData(): JsonResponse
    {
        try {
            $data = $this->checkoutService->getOrderSummary();
        } catch (CartIsEmptyException $e) {
            return ApiResponse::fail(
                $e->getMessage(),
                422,
            );
        }

        return ApiResponse::success(
            data: $data,
        );
    }
}
