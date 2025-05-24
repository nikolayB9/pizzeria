<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Cart\CartDetailedItemDto;
use App\DTO\Api\V1\Checkout\CheckoutSummaryDto;
use App\Exceptions\Cart\CartIsEmptyException;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\Profile\ProfileRepositoryInterface;
use App\Services\Traits\AuthenticatedUserTrait;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    use AuthenticatedUserTrait;

    public function __construct(private readonly ProfileRepositoryInterface $userRepository,
                                private readonly CartRepositoryInterface    $cartRepository,
                                private readonly CartService                $cartService)
    {
    }

    /**
     * Возвращает данные, необходимые для оформления заказа.
     *
     * @return CheckoutSummaryDto DTO с данными пользователя, товарами в корзине, общей стоимостью и слотами доставки.
     * @throws CartIsEmptyException Если корзина пуста.
     */
    public function getCheckoutData(): CheckoutSummaryDto
    {
        $userId = $this->userIdOrFail();

        $userData = $this->userRepository->getCheckoutInfo($userId);

        $cart = $this->getDetailedCartItemsOrThrowIfEmpty($userId);
        $cartTotal = $this->cartService->getTotalPrice($cart, false);
        $deliveryCost = $this->calculateDeliveryCostByCartTotal($cartTotal);
        $total = $this->calculateOrderTotal($cartTotal, $deliveryCost);

        $deliverySlots = $this->getDeliverySlots();

        return new CheckoutSummaryDto(
            user: $userData,
            cart: $cart,
            cart_total: $cartTotal,
            delivery_cost: $deliveryCost,
            total: $total,
            delivery_slots: $deliverySlots,
        );
    }

    /**
     * Возвращает стоимость доставки в зависимости от общей стоимости товаров в корзине.
     *
     * @param float $cartTotal Общая стоимость товаров в корзине.
     *
     * @return float Стоимость доставки (0.0 при сумме более 1000, иначе 100.0).
     */
    public function calculateDeliveryCostByCartTotal(float $cartTotal): float
    {
        return $cartTotal > 1000.0 ? 0.0 : 100.0;
    }

    /**
     * Возвращает итоговую стоимость заказа как сумму стоимости товаров и доставки.
     *
     * @param float $cartTotal Общая стоимость товаров в корзине.
     * @param float $deliveryCost Стоимость доставки.
     *
     * @return float Итоговая стоимость заказа.
     */
    public function calculateOrderTotal(float $cartTotal, float $deliveryCost): float
    {
        return $cartTotal + $deliveryCost;
    }

    /**
     * Возвращает полный набор данных о позициях корзины или выбрасывает исключение, если корзина пуста.
     *
     * @param int $userId ID пользователя, оформляющего заказ.
     *
     * @return CartDetailedItemDto[] Массив DTO с данными товаров в корзине.
     * @throws CartIsEmptyException Если корзина пуста.
     */
    protected function getDetailedCartItemsOrThrowIfEmpty(int $userId): array
    {
        $cartItems = $this->cartRepository->getDetailedCartItemsByIdentifier('user_id', $userId);

        if ($cartItems === []) {
            Log::warning('Не найдены продукты в корзине при попытке оформления заказа', [
                'user_id' => $userId,
                'method' => __METHOD__,
            ]);

            throw new CartIsEmptyException('Корзина пуста, невозможно продолжить оформление заказа.');
        }

        return $cartItems;
    }

    /**
     * Возвращает доступные временные слоты доставки.
     *
     * @return array<array{from: string, slot: string}> Массив слотов.
     */
    protected function getDeliverySlots(): array
    {
        $now = now();
        $minTime = $now->copy()->setTime(10, 0);
        $maxTime = $now->copy()->setTime(22, 0);
        $latestAvailableStart = $now->copy()->setTime(20, 30);

        // Время, начиная с которого можно формировать слоты (текущее + 45 мин)
        $candidateTime = $now->copy()->addMinutes(45);

        // Если текущее время вне допустимого диапазона или слишком поздно — начинать с 10:00 следующего дня
        if ($now->lt($minTime) || $now->gte($maxTime) || $candidateTime->gt($latestAvailableStart)) {
            $start = $now->copy()->addDay()->setTime(10, 0);
        } else {
            // Округление candidateTime до ближайших 15 минут вверх
            $minutes = $candidateTime->minute;
            $roundedMinutes = ceil($minutes / 15) * 15;

            // Установка округленного времени
            $start = $candidateTime->copy()->minute(0)->addMinutes($roundedMinutes);
            if ($roundedMinutes >= 60) {
                $start->addHour()->minute(0);
            }
        }

        // Генерация 5 слотов по 30 минут
        $slots = [];

        for ($i = 0; $i < 5; $i++) {
            $from = $start->copy()->addMinutes($i * 15);
            $to = $from->copy()->addMinutes(30);

            $slots[] = [
                'from' => $from->format('H:i'),
                'slot' => $from->format('H:i') . ' - ' . $to->format('H:i'),
            ];
        }

        return $slots;
    }
}
