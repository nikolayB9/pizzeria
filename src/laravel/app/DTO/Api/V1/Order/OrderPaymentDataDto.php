<?php

namespace App\DTO\Api\V1\Order;

class OrderPaymentDataDto
{
    public function __construct(
        public int   $id,
        public int   $user_id,
        public float $amount,
    )
    {
    }
}
