<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Cart\CartDetailedItemDto;
use App\DTO\Api\V1\Checkout\CheckoutSummaryDto;
use App\Exceptions\Cart\CartIsEmptyException;
use App\Exceptions\System\Order\MinDeliveryLeadTimeNotSetInConfigException;
use App\Exceptions\System\Order\MissingRequiredParameterInConfigException;
use App\Repositories\Api\V1\Cart\CartRepositoryInterface;
use App\Repositories\Api\V1\Profile\ProfileRepositoryInterface;
use App\Services\Traits\AuthenticatedUserTrait;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    use AuthenticatedUserTrait;

    public function __construct(
        private readonly ProfileRepositoryInterface $userRepository,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartService $cartService
    ) {
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
     * @throws MinDeliveryLeadTimeNotSetInConfigException Если в конфиге не указано минимальное время до доставки.
     */
    public function getDeliverySlots(): array
    {
        $now = now();

        // Временные границы дня для формирования слотов
        // минимально возможное время доставки сегодня
        $minTime = $now->copy()->setTime(10, 0);

        // после этого времени доставка уже не начнется сегодня
        $latestAvailableStart = $now->copy()->setTime(20, 30);

        $minDeliveryLeadTime = config('order.min_delivery_lead_time');
        $reserveTime = config('order.slot_reserve_time');
        $slotDuration = config('order.slot_duration');
        $slotInterval = config('order.slot_interval');

        $configValues = [
            'min_delivery_lead_time' => $minDeliveryLeadTime,
            'slot_reserve_time' => $reserveTime,
            'slot_duration' => $slotDuration,
            'slot_interval' => $slotInterval,
        ];

        foreach ($configValues as $key => $value) {
            if (is_null($value)) {
                throw new MissingRequiredParameterInConfigException(
                    "Не задан параметр конфигурации: order.{$key}"
                );
            }
        }

        // Время, начиная с которого потенциально возможна доставка (оформление + буфер)
        $candidateTime = now()->addMinutes($minDeliveryLeadTime + $reserveTime);

        // Если доставка сегодня невозможна (слишком рано или слишком поздно) — стартуем с 10:00 следующего дня
        if (
            $candidateTime->lt($minTime) ||
            $candidateTime->gt($latestAvailableStart)
        ) {
            $start = now()->copy()->addDay()->setTime(10, 0);
        } else {
            // Округление candidateTime вверх до ближайшего 15-минутного интервала
            $start = $candidateTime->copy()->addMinutes((15 - $candidateTime->minute % 15) % 15);
        }

        // Формируем 5 слотов по 30 минут, старт каждого через 15 минут от предыдущего
        $slots = [];

        for ($i = 0; $i < 5; $i++) {
            $from = $start->copy()->addMinutes($i * $slotInterval);
            $to = $from->copy()->addMinutes($slotDuration);

            $slots[] = [
                'from' => $from->format('H:i'),
                'slot' => $from->format('H:i') . ' - ' . $to->format('H:i'),
            ];
        }

        return $slots;
    }


}
