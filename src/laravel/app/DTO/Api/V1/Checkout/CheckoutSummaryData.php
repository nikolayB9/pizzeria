<?php

namespace App\DTO\Api\V1\Checkout;

class CheckoutSummaryData
{
    public function __construct(
        public float $cart_total,
        public float $delivery_cost,
        public float $total,
    )
    {
    }
}
