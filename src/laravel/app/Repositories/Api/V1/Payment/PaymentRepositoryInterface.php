<?php

namespace App\Repositories\Api\V1\Payment;

use App\DTO\Api\V1\Payment\CreatePaymentDto;
use App\Enums\Payment\PaymentStatusEnum;

interface PaymentRepositoryInterface
{
    public function createPayment(CreatePaymentDto $dto): void;

    /**
     * Проверяет существование платежа с заданными параметрами.
     *
     * @param array<string, mixed> $searchFields
     *
     * @return bool
     */
    public function exists(array $searchFields): bool;

    public function changePaymentStatus(int $paymentId, PaymentStatusEnum $newStatus): void;
}
