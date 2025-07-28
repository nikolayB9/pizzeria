<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Order\CreateOrderDto;
use App\DTO\Api\V1\Order\CreateOrderInputDto;
use App\DTO\Api\V1\Order\MinifiedOrderDataDto;
use App\DTO\Api\V1\Order\OrderDto;
use App\DTO\Api\V1\Order\PaginatedOrderListDto;
use App\DTO\Api\V1\Payment\CreatePaymentDto;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Exceptions\Domain\Order\OrderCreationFailedException;
use App\Exceptions\Order\InvalidDeliveryTimeException;
use App\Exceptions\Order\MinDeliveryLeadTimeNotSetInConfigException;
use App\Exceptions\Order\OrderNotFoundException;
use App\Exceptions\Order\OrderNotReadyForPaymentException;
use App\Exceptions\Payment\PaymentNotCreateException;
use App\Exceptions\Payment\PaymentNotFoundException;
use App\Exceptions\Payment\PaymentNotUpdatedException;
use App\Exceptions\User\OrdersPerPageNotSetInConfigException;
use App\Repositories\Api\V1\Cart\CartRepositoryInterface;
use App\Repositories\Api\V1\Order\OrderRepositoryInterface;
use App\Repositories\Api\V1\Payment\PaymentRepositoryInterface;
use App\Repositories\Api\V1\Profile\ProfileRepositoryInterface;
use App\Services\Api\V1\Gateway\PaymentGatewayInterface;
use App\Services\Traits\AuthenticatedUserTrait;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    use AuthenticatedUserTrait;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProfileRepositoryInterface $userRepository,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly PaymentGatewayInterface $paymentGateway
    ) {
    }

    /**
     * Возвращает список заказов авторизованного пользователя для указанной страницы.
     *
     * @param int|null $page Номер страницы для пагинации (null — первая страница).
     *
     * @return PaginatedOrderListDto Список заказов и информация о пагинации.
     * @throws OrdersPerPageNotSetInConfigException Если не задан параметр orders_per_page в конфиге.
     */
    public function getUserOrders(?int $page): PaginatedOrderListDto
    {
        $userId = $this->userIdOrFail();
        $ordersPerPage = config('user.orders_per_page');

        if (is_null($ordersPerPage)) {
            throw new OrdersPerPageNotSetInConfigException(
                'Не задан параметр конфига [orders_per_page] в файле [user].'
            );
        }

        return $this->orderRepository->getPaginatedOrderListByUserId($userId, $page, $ordersPerPage);
    }

    /**
     * Возвращает данные заказа по его ID для авторизованного пользователя.
     *
     * @param int $orderId ID пользователя.
     *
     * @return OrderDto DTO с данными для отображения подробной информации о заказе.
     * @throws OrderNotFoundException Если заказ не найден или не принадлежит пользователю.
     */
    public function getUserOrder(int $orderId): OrderDto
    {
        $userId = $this->userIdOrFail();

        return $this->orderRepository->getUserOrderById($userId, $orderId);
    }

    /**
     * @throws PaymentNotUpdatedException
     * @throws PaymentNotFoundException
     * @throws PaymentNotCreateException
     * @throws OrderCreationFailedException
     */
    public function createOrderWithPayment(CreateOrderInputDto $dto): string
    {
        // Создание заказа со статусом CREATED
        $orderData = $this->buildOrderData($dto);
        $order = $this->orderRepository->createOrder($orderData);

        // Создание платежа со статусом CREATED
        // Изменение статуса заказа на WAITING_PAYMENT (в транзакции)
        $paymentData = $this->buildPaymentData($order);
        $payment = $this->paymentRepository->createPayment($paymentData);

        // Создание платежа в Юкассе
        $gatewayData = $this->paymentGateway->initiatePayment($payment);

        // Меняем статус платежа на WAITING_CAPTURE
        $this->paymentRepository->applyGatewayResponse(
            $payment->payment_id,
            $gatewayData,
            PaymentStatusEnum::WAITING_CAPTURE
        );

        // Возвращаем ссылку на оплату
        return $gatewayData->confirmation_url;
    }

    /**
     * Преобразует строку времени доставки в объект DateTime и валидирует отставание от текущего времени.
     *
     * @param string $deliveryTime Время доставки в формате 'H:i' (например, '13:45').
     *
     * @return DateTimeImmutable Объект с полной датой и временем доставки.
     * @throws MinDeliveryLeadTimeNotSetInConfigException Если минимальное время до доставки не задано в конфиге.
     * @throws InvalidDeliveryTimeException Если неверное время доставки.
     */
    public function parseAndValidateDeliveryTime(string $deliveryTime): DateTimeImmutable
    {
        // Парсим время доставки (часы и минуты)
        [$hour, $minute] = explode(':', $deliveryTime);
        $now = now();

        // Формируем предполагаемое время доставки на сегодня
        $candidate = $now->copy()->setTime((int)$hour, (int)$minute, 0);

        // Если время уже прошло — переносим на завтра
        if ($candidate->lessThanOrEqualTo($now)) {
            $candidate = $candidate->addDay();
        }

        $minDeliveryLeadTime = config('order.min_delivery_lead_time');

        if (is_null($minDeliveryLeadTime)) {
            throw new MinDeliveryLeadTimeNotSetInConfigException(
                'Не задано минимальное время от оформления заказа до доставки.'
            );
        }

        if ($now->diffInMinutes($candidate, false) < $minDeliveryLeadTime) {
            throw new InvalidDeliveryTimeException(
                "Время доставки должно быть не менее чем через $minDeliveryLeadTime минут."
            );
        }

        return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $candidate->format('Y-m-d H:i:s'));
    }

    /**
     * @throws OrderCreationFailedException
     */
    private function buildOrderData(CreateOrderInputDto $dto): CreateOrderDto
    {
        $userId = $this->userIdOrFail();
        $addressId = $this->userRepository->getDefaultAddressId($userId);

        if (is_null($addressId)) {
            throw new OrderCreationFailedException('Для оформления заказа добавьте адрес доставки.', 422);
        }

        try {
            $deliveryAt = $this->parseAndValidateDeliveryTime($dto->delivery_time);
        } catch (InvalidDeliveryTimeException $e) {
            throw new OrderCreationFailedException($e->getMessage(), 422);
        }

        $cart = $this->cartRepository->getRawCartItemsByIdentifier('user_id', $userId);

        if (empty($cart)) {
            Log::warning('Не найдены продукты в корзине при попытке создания заказа', [
                'user_id' => $userId,
                'method' => __METHOD__,
            ]);

            throw new OrderCreationFailedException('Корзина пуста, невозможно создать заказ.', 422);
        }

        $cartTotal = $this->cartService->getTotalPrice($cart);
        $deliveryCost = $this->checkoutService->calculateDeliveryCostByCartTotal($cartTotal);
        $total = $this->checkoutService->calculateOrderTotal($cartTotal, $deliveryCost);

        return new CreateOrderDto(
            user_id: $userId,
            address_id: $addressId,
            delivery_cost: $deliveryCost,
            total: $total,
            status: OrderStatusEnum::CREATED,
            delivery_at: $deliveryAt,
            comment: $dto->comment,
            cart: $cart,
        );
    }

    private function buildPaymentData(MinifiedOrderDataDto $dto): CreatePaymentDto
    {
        $idempotenceKey = (string)Str::uuid();

        return new CreatePaymentDto(
            order_id: $dto->order_id,
            status: PaymentStatusEnum::CREATED,
            amount: $dto->amount,
            idempotence_key: $idempotenceKey
        );
    }
}
