<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Domain\ExternalPayment\ExternalPaymentCreationFailedException;
use App\Exceptions\Domain\Order\OrderCreationFailedException;
use App\Exceptions\Domain\Payment\PaymentCreationFailedException;
use App\Exceptions\Domain\Payment\PaymentGatewayResponseApplyFailedException;
use App\Exceptions\Order\OrderNotFoundException;
use App\Http\Requests\Api\V1\Order\IndexOrderRequest;
use App\Http\Requests\Api\V1\Order\StoreOrderRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Api\V1\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    /**
     * Возвращает список заказов авторизованного пользователя для переданной страницы.
     *
     * @param IndexOrderRequest $request Валидированные данные запроса (номер страницы).
     *
     * @return JsonResponse JSON-ответ со списком заказов и информацией о пагинации.
     */
    public function index(IndexOrderRequest $request): JsonResponse
    {
        $page = $request->validated()['page'] ?? null;

        $paginatedOrders = $this->orderService->getUserOrders($page);

        return ApiResponse::success(
            data: $paginatedOrders->data,
            meta: $paginatedOrders->meta,
        );
    }

    /**
     * Возвращает данные заказа для авторизованного пользователя.
     *
     * @param int $id ID заказа.
     *
     * @return JsonResponse JSON-ответ с данными заказа.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getUserOrder($id);
        } catch (OrderNotFoundException $e) {
            return ApiResponse::fail($e->getMessage(), 404);
        }

        return ApiResponse::success(data: $order);
    }

    /**
     * Создает заказ и возвращает ссылку на оплату.
     *
     * @param StoreOrderRequest $request Валидированные данные запроса для создания заказа.
     *
     * @return JsonResponse JSON-ответ со ссылкой на оплату.
     * @throws OrderCreationFailedException Если заказ не был создан.
     * @throws PaymentCreationFailedException Если платеж заказа не был создан.
     * @throws ExternalPaymentCreationFailedException Если платеж во внешнем сервисе не был создан.
     * @throws PaymentGatewayResponseApplyFailedException Если ответ платежного шлюза не был применен к платежу.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $paymentUrl = $this->orderService->createOrderWithPayment($request->toDto());

        return ApiResponse::success(['payment_url' => $paymentUrl]);
    }
}
