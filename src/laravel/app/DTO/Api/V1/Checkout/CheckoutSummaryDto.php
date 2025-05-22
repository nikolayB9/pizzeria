<?php

namespace App\DTO\Api\V1\Checkout;

class CheckoutSummaryDto
{
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
