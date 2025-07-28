<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Cart\CartIsEmptyException;
use App\Exceptions\Order\InvalidDeliveryTimeException;
use App\Exceptions\Order\OrderNotCreateException;
use App\Exceptions\Order\OrderNotFoundException;
use App\Exceptions\Order\OrderNotReadyForPaymentException;
use App\Exceptions\Payment\PaymentNotCreateException;
use App\Exceptions\User\MissingDefaultUserAddressException;
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
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $paymentUrl = $this->orderService->createOrderWithPayment($request->toDto());
        } catch (MissingDefaultUserAddressException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                status: 404,
                meta: ['order_created' => false],
            );
        } catch (InvalidDeliveryTimeException|CartIsEmptyException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                status: 422,
                meta: ['order_created' => false],
            );
        } catch (OrderNotCreateException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                status: 500,
                meta: ['order_created' => false],
            );
        } catch (OrderNotReadyForPaymentException|PaymentNotCreateException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                status: 500,
                meta: ['order_created' => true],
            );
        }

        return ApiResponse::success(['payment_url' => $paymentUrl]);
    }
}
