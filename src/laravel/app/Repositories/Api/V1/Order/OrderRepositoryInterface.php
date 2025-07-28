<?php

namespace App\Repositories\Api\V1\Order;

use App\DTO\Api\V1\Order\CreateOrderDto;
use App\DTO\Api\V1\Order\MinifiedOrderDataDto;
use App\DTO\Api\V1\Order\OrderDto;
use App\DTO\Api\V1\Order\OrderWithPaymentDto;
use App\DTO\Api\V1\Order\PaginatedOrderListDto;
use App\Enums\Order\OrderStatusEnum;
use App\Exceptions\Domain\Order\OrderCreationFailedException;
use App\Exceptions\Order\OrderNotFoundException;
use App\Exceptions\Order\OrderStatusNotUpdatedException;

interface OrderRepositoryInterface
{
    /**
     * Получает список заказов пользователя с постраничной разбивкой.
     *
     * @param int $userId
     * @param int|null $page
     * @param int $countPerPage
     *
     * @return PaginatedOrderListDto
     */
    public function getPaginatedOrderListByUserId(int $userId, ?int $page, int $countPerPage): PaginatedOrderListDto;

    /**
     * Возвращает данные заказа пользователя по его ID.
     *
     * @param int $userId
     * @param int $orderId
     *
     * @return OrderDto
     * @throws OrderNotFoundException
     */
    public function getUserOrderById(int $userId, int $orderId): OrderDto;

    /**
     * Создает заказ и очищает корзину.
     *
     * @param CreateOrderDto $data
     *
     * @return MinifiedOrderDataDto
     * @throws OrderCreationFailedException
     */
    public function createOrder(CreateOrderDto $data): MinifiedOrderDataDto;

    /**
     * Изменяет статус заказа пользователя.
     *
     * @param int $orderId
     * @param OrderStatusEnum $newStatus
     *
     * @return void
     * @throws OrderNotFoundException
     * @throws OrderStatusNotUpdatedException
     */
    public function updateStatus(int $orderId, OrderStatusEnum $newStatus): void;

    /**
     * Проверяет существование заказа с заданными параметрами.
     *
     * @param array<string, mixed> $searchFields
     *
     * @return bool
     */
    public function exists(array $searchFields): bool;

    public function getOrderWithPayment(int $orderId): OrderWithPaymentDto;
}
