<?php

namespace App\DTO\Api\V1\Checkout;

use App\DTO\Api\V1\Cart\CartDetailedItemDto;

class CheckoutSummaryDto
{

    /**
     * @param CheckoutUserDataDto $user
     * @param CartDetailedItemDto[] $cart
     * @param float $cart_total
     * @param float $delivery_cost
     * @param float $total
     * @param array<array{from: string, slot: string}> $delivery_slots
     */
    public function __construct(
        public CheckoutUserDataDto $user,
        public array               $cart,
        public float               $cart_total,
        public float               $delivery_cost,
        public float               $total,
        public array               $delivery_slots,
    )
    {
    }
}
