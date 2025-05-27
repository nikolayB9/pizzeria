<?php

namespace App\Repositories\Api\V1\Order;

use App\DTO\Api\V1\Order\CreateOrderDto;
use App\DTO\Api\V1\Order\OrderDto;
use App\DTO\Api\V1\Order\PaginatedOrderListDto;
use App\Exceptions\Order\OrderNotCreateException;
use App\Exceptions\Order\OrderNotFoundException;

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
     * @param int $userId
     * @param CreateOrderDto $data
     *
     * @return void
     * @throws OrderNotCreateException
     */
    public function createOrder(int $userId, CreateOrderDto $data): void;
}
