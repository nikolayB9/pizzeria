<?php

namespace App\Repositories\Admin;

use App\DTO\Admin\Order\PaginatedOrderListDto;
use App\Exceptions\Order\OrderNotFoundException;
use App\Exceptions\Order\OrderStatusNotUpdatedException;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class EloquentAdminOrderRepository implements AdminOrderRepositoryInterface
{
    /**
     * Возвращает список заказов пользователей с пагинацией.
     *
     * @param int $countPerPage Количество заказов на странице.
     * @param int|null $page Номер страницы, если null — будет использована первая страница.
     *
     * @return PaginatedOrderListDto Список заказов и данные пагинации.
     */
    public function getPaginatedOrders(int $countPerPage, ?int $page = null): PaginatedOrderListDto
    {
        $orders = Order::latest()
            ->select(['id', 'user_id', 'total', 'status', 'created_at', 'delivery_at'])
            ->with('user:id,email')
            ->paginate($countPerPage, ['*'], 'page', $page);

        return PaginatedOrderListDto::fromPaginator($orders);
    }

    /**
     * Обновляет статус заказа по его ID.
     *
     * @param int $id ID заказа.
     * @param int $status Значение нового статуса (OrderStatusEnum::value).
     *
     * @return void
     * @throws OrderNotFoundException Если заказ с указанным ID не найден.
     * @throws OrderStatusNotUpdatedException Если произошла ошибка при обновлении статуса.
     */
    public function updateStatusById(int $id, int $status): void
    {
        try {
            $order = Order::findOrFail($id);
            $order->status = $status;
            $order->save();
        } catch (ModelNotFoundException $e) {
            Log::warning('Не найден заказ при попытке изменить его статус', [
                'order_id' => $id,
                'status' => $status,
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            throw new OrderNotFoundException("Заказ с ID [$id] не найден.");
        } catch (\Throwable $e) {
            Log::error('Не удалось изменить статус заказа', [
                'order_id' => $id,
                'status' => $status,
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            throw new OrderStatusNotUpdatedException();
        }
    }
}
