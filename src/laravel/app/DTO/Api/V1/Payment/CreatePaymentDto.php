<?php

namespace App\DTO\Api\V1\Payment;

use App\Enums\Payment\PaymentStatusEnum;

class CreatePaymentDto
{
    public function __construct(
        public int $order_id,
        public PaymentStatusEnum $status,
        public float $amount,
        public string $idempotence_key,
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
            'status' => $this->status,
            'amount' => $this->amount,
            'idempotence_key' => $this->idempotence_key,
        ];
    }
}
