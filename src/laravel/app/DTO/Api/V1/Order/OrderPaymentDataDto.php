<?php

namespace App\DTO\Api\V1\Order;

class OrderPaymentDataDto
{
    public function __construct(
        public int   $id,
        public float $total,
    )
    {
    }
}
