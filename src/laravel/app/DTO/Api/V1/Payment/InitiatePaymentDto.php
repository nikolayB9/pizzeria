<?php

namespace App\DTO\Api\V1\Payment;

use App\Enums\Payment\PaymentGatewayEnum;

class InitiatePaymentDto
{
    public function __construct(
        public string $gateway_payment_id,
        public string $confirmation_url,
        public array $metadata,
        public array $raw,
        public PaymentGatewayEnum $gateway,
    ) {
    }
}
