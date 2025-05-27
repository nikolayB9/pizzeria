<?php

namespace App\Services\Admin;

use App\DTO\Admin\Order\PaginatedOrderListDto;
use App\Exceptions\Order\OrderNotFoundException;
use App\Exceptions\Order\OrderStatusNotUpdatedException;
use App\Repositories\Admin\AdminOrderRepositoryInterface;

class AdminOrderService
{
    public function __construct(private readonly AdminOrderRepositoryInterface $adminOrderRepository)
    {
    }

    /**
     * Получает список заказов с пагинацией для административной панели.
     *
     * @param int|null $page Номер страницы (null — первая страница).
     *
     * @return PaginatedOrderListDto DTO с заказами и информацией о пагинации.
     */
    public function getOrders(?int $page): PaginatedOrderListDto
    {
        $countPerPage = config('admin.orders_per_page', 15);

        return $this->adminOrderRepository->getPaginatedOrders($countPerPage, $page);
    }

    /**
     * Обновляет статус заказа по его ID.
     *
     * @param int $orderId ID заказа.
     * @param int $status Новое значение статуса (OrderStatusEnum::value).
     *
     * @return void
     * @throws OrderNotFoundException Если заказ с указанным ID не найден.
     * @throws OrderStatusNotUpdatedException Если произошла ошибка при обновлении статуса.
     */
    public function updateOrderStatus(int $orderId, int $status): void
    {
        $this->adminOrderRepository->updateStatusById($orderId, $status);
    }
}
