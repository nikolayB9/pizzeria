<?php

namespace App\DTO\Api\V1\Order;

use App\Enums\Order\OrderStatusEnum;

class MinifiedOrderDataDto
{
    public function __construct(
        public int $order_id,
        public int $user_id,
        public float $amount,
        public OrderStatusEnum $status,
    ) {
    }
}
