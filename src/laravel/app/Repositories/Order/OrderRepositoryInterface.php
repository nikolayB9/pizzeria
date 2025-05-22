<?php

namespace App\Repositories\Order;

use App\DTO\Api\V1\Order\CreateOrderDto;
use App\Exceptions\Order\OrderNotCreateException;

interface OrderRepositoryInterface
{
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
