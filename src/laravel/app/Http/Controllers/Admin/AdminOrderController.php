<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Order\OrderStatusEnum;
use App\Exceptions\Order\OrderNotFoundException;
use App\Exceptions\System\Order\OrderStatusNotUpdatedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\IndexOrderRequest;
use App\Http\Requests\Admin\Order\UpdateStatusRequest;
use App\Services\Admin\AdminOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function __construct(private readonly AdminOrderService $adminOrderService)
    {
    }

    /**
     * Отображает список заказов в админ-панели.
     *
     * @param IndexOrderRequest $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(IndexOrderRequest $request): View
    {
        $page = $request->validated()['page'] ?? null;

        $data = $this->adminOrderService->getOrders($page);

        return view('admin.order.index', [
            'orders' => $data->list,
            'meta' => $data->meta,
            'statuses' => OrderStatusEnum::cases(),
        ]);
    }

    /**
     * Обновляет статус заказа по его ID.
     *
     * @param int $id ID заказа.
     * @param UpdateStatusRequest $request Валидированный статус заказа.
     *
     * @return JsonResponse
     */
    public function updateStatus(int $id, UpdateStatusRequest $request): JsonResponse
    {
        try {
            $this->adminOrderService->updateOrderStatus($id, $request->validated()['status']);
        } catch (OrderNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (OrderStatusNotUpdatedException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Статус успешно обновлен']);
    }
}
