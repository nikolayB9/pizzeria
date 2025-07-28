<?php

namespace App\Services\Api\V1\Gateway;

use App\DTO\Api\V1\Order\OrderPaymentDataDto;
use App\DTO\Api\V1\Payment\PaymentDto;
use App\Exceptions\Payment\PaymentNotCreateException;

interface PaymentGatewayInterface
{
    /**
     * Создает платеж для заказа и возвращает ссылку на оплату.
     *
     * @param OrderPaymentDataDto $orderData
     *
     * @return string
     * @throws PaymentNotCreateException
     */
    public function createPaymentForOrder(OrderPaymentDataDto $orderData): string;

    public function getPayment(string $gatewayPaymentId): PaymentDto;
}
