<?php

namespace App\DTO\Api\V1\Payment;

use App\Enums\Payment\PaymentStatusEnum;

class PaymentDto
{
    public function __construct(
        public string            $gateway_payment_id,
        public int               $order_id,
        public PaymentStatusEnum $status,
        public float             $amount,
    )
    {
    }
}
