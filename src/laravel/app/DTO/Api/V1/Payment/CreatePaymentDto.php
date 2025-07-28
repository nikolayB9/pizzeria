<?php

namespace App\DTO\Api\V1\Payment;

use App\Enums\Payment\PaymentGatewayEnum;
use App\Enums\Payment\PaymentStatusEnum;

class CreatePaymentDto
{
    public function __construct(
        public int $order_id,
        public int $user_id,
        public string $gateway_payment_id,
        public PaymentStatusEnum $status,
        public PaymentGatewayEnum $gateway,
        public float $amount,
        public string $idempotence_key,
        public array $metadata,
        public array $raw_response,
    ) {
    }

    /**
     * Преобразует DTO в массив для вставки в БД.
     *
     * @return array<string, mixed> Массив для вставки в БД.
     */
    public function toInsertArray(): array
    {
        return [
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'gateway_payment_id' => $this->gateway_payment_id,
            'status' => $this->status,
            'gateway' => $this->gateway,
            'amount' => $this->amount,
            'idempotence_key' => $this->idempotence_key,
            'metadata' => $this->metadata,
            'raw_response' => $this->raw_response,
        ];
    }
}
