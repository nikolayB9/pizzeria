<?php

namespace App\Repositories\Admin;

use App\DTO\Admin\Order\PaginatedOrderListDto;
use App\Exceptions\Order\OrderNotFoundException;
use App\Exceptions\System\Order\OrderStatusNotUpdatedException;

interface AdminOrderRepositoryInterface
{
    /**
     * Возвращает список заказов пользователей с пагинацией.
     *
     * @param int $countPerPage
     * @param int|null $page
     *
     * @return PaginatedOrderListDto
     */
    public function getPaginatedOrders(int $countPerPage, ?int $page = null): PaginatedOrderListDto;

    /**
     * Обновляет статус заказа по его ID.
     *
     * @param int $id
     * @param int $status
     *
     * @return void
     * @throws OrderNotFoundException
     * @throws OrderStatusNotUpdatedException
     */
    public function updateStatusById(int $id, int $status): void;
}
