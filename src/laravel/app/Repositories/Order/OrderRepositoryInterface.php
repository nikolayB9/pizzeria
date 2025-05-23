<?php

namespace App\Repositories\Order;

use App\DTO\Api\V1\Order\CreateOrderDto;
use App\DTO\Api\V1\Order\PaginatedOrderListDto;
use App\Exceptions\Order\OrderNotCreateException;

interface OrderRepositoryInterface
{
    /**
     * Получает список заказов пользователя с постраничной разбивкой.
     *
     * @param int $userId
     * @param int|null $page
     *
     * @return PaginatedOrderListDto
     */
    public function getPaginatedOrderListByUserId(int $userId, ?int $page): PaginatedOrderListDto;

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
