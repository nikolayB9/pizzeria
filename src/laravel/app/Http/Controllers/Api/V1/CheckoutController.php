<?php

namespace App\Http\Controllers\Api\V1;

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
    public function getUserData(): JsonResponse
    {
        $checkoutData = $this->checkoutService->getCheckoutUserData();

        return ApiResponse::success(
            data: $checkoutData
        );
    }
}
