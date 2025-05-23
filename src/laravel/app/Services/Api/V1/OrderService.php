<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Cart\CartRawItemDto;
use App\DTO\Api\V1\Order\CreateOrderDto;
use App\DTO\Api\V1\Order\CreateOrderInputDto;
use App\DTO\Api\V1\Order\PaginatedOrderListDto;
use App\Enums\Order\OrderStatusEnum;
use App\Exceptions\Cart\CartIsEmptyException;
use App\Exceptions\Order\InvalidDeliveryTimeException;
use App\Exceptions\Order\OrderNotCreateException;
use App\Exceptions\User\MissingDefaultUserAddressException;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Traits\AuthenticatedUserTrait;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;

class OrderService
{
    use AuthenticatedUserTrait;

    public function __construct(private readonly OrderRepositoryInterface $orderRepository,
                                private readonly UserRepositoryInterface  $userRepository,
                                private readonly CartRepositoryInterface  $cartRepository,
                                private readonly CartService              $cartService,
                                private readonly CheckoutService          $checkoutService)
    {
    }

    /**
     * Возвращает список заказов авторизованного пользователя для указанной страницы.
     *
     * @param int|null $page Номер страницы для пагинации (null — первая страница).
     *
     * @return PaginatedOrderListDto Список заказов и информация о пагинации.
     */
    public function getUserOrders(?int $page): PaginatedOrderListDto
    {
        $userId = $this->userIdOrFail();

        return $this->orderRepository->getPaginatedOrderListByUserId($userId, $page);
    }

    /**
     * Создает заказ для текущего пользователя.
     *
     * @param CreateOrderInputDto $requestDto Валидированные данные от пользователя для оформления заказа.
     *
     * @return void
     * @throws MissingDefaultUserAddressException Если дефолтный адрес доставки не найден.
     * @throws InvalidDeliveryTimeException Если до выбранного времени доставки осталось меньше 40 минут.
     * @throws CartIsEmptyException Если корзина пуста.
     * @throws OrderNotCreateException Если произошла ошибка при создании заказа.
     */
    public function storeOrder(CreateOrderInputDto $requestDto): void
    {
        $userId = $this->userIdOrFail();

        $addressId = $this->userRepository->getDefaultAddressIdOrThrow($userId);
        $deliveryAt = $this->parseAndValidateDeliveryTime($requestDto->delivery_time);

        $cart = $this->getRawCartItemsOrThrowIfEmpty($userId);
        $cartTotal = $this->cartService->getTotalPrice($cart);
        $deliveryCost = $this->checkoutService->calculateDeliveryCostByCartTotal($cartTotal);
        $total = $this->checkoutService->calculateOrderTotal($cartTotal, $deliveryCost);

        $orderData = new CreateOrderDto(
            user_id: $userId,
            address_id: $addressId,
            delivery_cost: $deliveryCost,
            total: $total,
            status: OrderStatusEnum::CREATED,
            delivery_at: $deliveryAt,
            comment: $requestDto->comment,
            cart: $cart,
        );

        $this->orderRepository->createOrder($userId, $orderData);
    }

    /**
     * Возвращает минимальный набор данных о позициях корзины или выбрасывает исключение, если корзина пуста.
     *
     * @param int $userId ID пользователя, оформляющего заказ.
     *
     * @return CartRawItemDto[] Массив DTO с данными товаров в корзине.
     * @throws CartIsEmptyException Если корзина пуста.
     */
    protected function getRawCartItemsOrThrowIfEmpty(int $userId): array
    {
        $cartItems = $this->cartRepository->getRawCartItemsByIdentifier('user_id', $userId);

        if ($cartItems === []) {
            Log::warning('Не найдены продукты в корзине при попытке создания заказа', [
                'user_id' => $userId,
                'method' => __METHOD__,
            ]);

            throw new CartIsEmptyException('Корзина пуста, невозможно создать заказ.');
        }

        return $cartItems;
    }

    /**
     * Преобразует строку времени доставки в объект DateTime и валидирует отставание от текущего времени.
     *
     * @param string $deliveryTime Время доставки в формате 'H:i' (например, '13:45').
     *
     * @return DateTimeImmutable Объект с полной датой и временем доставки.
     * @throws InvalidDeliveryTimeException Если время доставки менее чем через 40 минут.
     */
    protected function parseAndValidateDeliveryTime(string $deliveryTime): DateTimeImmutable
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

        // Проверка: не менее 40 минут от текущего времени
        if ($now->diffInMinutes($candidate, false) < 40) {
            throw new InvalidDeliveryTimeException("Время доставки должно быть не менее чем через 40 минут.");
        }

        return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $candidate->format('Y-m-d H:i:s'));
    }
}
