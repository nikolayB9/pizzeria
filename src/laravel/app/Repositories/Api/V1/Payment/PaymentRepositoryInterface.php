<?php

namespace App\Repositories\Api\V1\Payment;

use App\DTO\Api\V1\Payment\CreatePaymentDto;
use App\DTO\Api\V1\Payment\InitiatePaymentDto;
use App\DTO\Api\V1\Payment\MinifiedPaymentDataDto;
use App\Enums\Payment\PaymentStatusEnum;
use App\Exceptions\Domain\Payment\PaymentCreationFailedException;
use App\Exceptions\Domain\Payment\PaymentGatewayResponseApplyFailedException;

interface PaymentRepositoryInterface
{
    /**
     * @throws PaymentCreationFailedException
     */
    public function createPayment(CreatePaymentDto $dto): MinifiedPaymentDataDto;

    /**
     * @throws PaymentGatewayResponseApplyFailedException
     */
    public function applyGatewayResponse(int $paymentId, InitiatePaymentDto $dto, PaymentStatusEnum $status): void;

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
