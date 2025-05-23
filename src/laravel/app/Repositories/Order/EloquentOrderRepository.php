<?php

namespace App\Repositories\Order;

use App\DTO\Api\V1\Cart\CartRawItemDto;
use App\DTO\Api\V1\Order\CreateOrderDto;
use App\DTO\Api\V1\Order\OrderDto;
use App\DTO\Api\V1\Order\PaginatedOrderListDto;
use App\Exceptions\Order\OrderNotCreateException;
use App\Exceptions\Order\OrderNotFoundException;
use App\Models\Order;
use App\Repositories\Cart\CartRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function __construct(private readonly CartRepositoryInterface $cartRepository)
    {
    }

    /**
     * Получает список заказов пользователя с постраничной разбивкой.
     *
     * @param int $userId ID пользователя.
     * @param int|null $page Номер страницы для пагинации (null — первая страница).
     *
     * @return PaginatedOrderListDto Список заказов и информация о пагинации.
     */
    public function getPaginatedOrderListByUserId(int $userId, ?int $page): PaginatedOrderListDto
    {
        return PaginatedOrderListDto::fromPaginator(
            Order::where('user_id', $userId)
                ->select(['id', 'user_id', 'address_id', 'total', 'status', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->with([
                    'address:id,city_id,street_id,house,is_default',
                    'products:id,product_id',
                    'products.product:id',
                    'products.product.previewImage',
                ])
                ->paginate(10, ['*'], 'page', $page)
        );
    }

    /**
     * Возвращает данные заказа пользователя по его ID.
     *
     * @param int $userId ID пользователя.
     * @param int $orderId ID заказа.
     *
     * @return OrderDto DTO с данными для отображения подробной информации о заказе.
     * @throws OrderNotFoundException Если заказ не найден или не принадлежит пользователю.
     */
    public function getUserOrderById(int $userId, int $orderId): OrderDto
    {
        try {
            return OrderDto::fromModel(
                Order::where('id', $orderId)
                    ->where('user_id', $userId)
                    ->select(['id', 'user_id', 'address_id', 'total', 'delivery_cost', 'status', 'created_at'])
                    ->with([
                        'address:id,city_id,street_id,house,is_default',
                        'products:id,product_id,name',
                        'products.product:id,name',
                        'products.product.previewImage',
                    ])
                    ->firstOrFail()
            );
        } catch (ModelNotFoundException $e) {
            Log::warning('Не найден заказ пользователя', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'method' => __METHOD__,
                'exception' => $e->getMessage(),
            ]);

            throw new OrderNotFoundException('Заказ не найден.');
        }
    }

    /**
     * Создает заказ и очищает корзину.
     *
     * @param int $userId ID пользователя.
     * @param CreateOrderDto $data Данные, необходимые для создания заказа.
     *
     * @return void
     * @throws OrderNotCreateException Если произошла ошибка при создании заказа.
     */
    public function createOrder(int $userId, CreateOrderDto $data): void
    {
        try {
            DB::transaction(function () use ($userId, $data) {
                $order = Order::create($data->toInsertArray());

                $products = CartRawItemDto::toOrderProductInsertData($data->cart);
                $order->products()->attach($products);

                $this->cartRepository->clearCartByIdentifier('user_id', $userId);
            });
        } catch (\Throwable $e) {
            Log::error('Ошибка при создании заказа', [
                'user_id' => $userId,
                'order_data' => Arr::except($data->toInsertArray(), ['comment']),
                'method' => __METHOD__,
                'exception' => $e->getMessage(),
            ]);

            throw new OrderNotCreateException('Не удалось создать заказ. Пожалуйста, попробуйте снова.');
        }
    }
}
