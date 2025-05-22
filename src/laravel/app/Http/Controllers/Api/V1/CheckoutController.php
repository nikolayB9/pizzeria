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
     * Возвращает предварительные данные для оформления заказа.
     *
     * @return JsonResponse JSON-ответ с необходимыми данными.
     */
    public function show(): JsonResponse
    {
        try {
            $data = $this->checkoutService->getCheckoutData();
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
