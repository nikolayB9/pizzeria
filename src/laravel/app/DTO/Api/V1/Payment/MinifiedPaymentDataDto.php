<?php

namespace App\DTO\Api\V1\Payment;

use App\Enums\Payment\PaymentStatusEnum;

class MinifiedPaymentDataDto
{
    public function __construct(
        public int $payment_id,
        public int $order_id,
        public PaymentStatusEnum $status,
        public float $amount,
        public string $idempotence_key,
    ) {
    }
}
