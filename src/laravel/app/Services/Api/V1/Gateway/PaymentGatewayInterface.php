<?php

namespace App\Services\Api\V1\Gateway;

use App\DTO\Api\V1\Payment\InitiatePaymentDto;
use App\DTO\Api\V1\Payment\MinifiedPaymentDataDto;
use App\DTO\Api\V1\Payment\PaymentDto;
use App\Exceptions\Payment\PaymentNotCreateException;

interface PaymentGatewayInterface
{
    /**
     * Создает платеж для заказа и возвращает ссылку на оплату.
     *
     * @throws PaymentNotCreateException
     */
    public function initiatePayment(MinifiedPaymentDataDto $dto): InitiatePaymentDto;

    public function getPayment(string $gatewayPaymentId): PaymentDto;
}
