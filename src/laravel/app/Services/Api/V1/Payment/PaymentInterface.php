<?php

namespace App\Services\Api\V1\Payment;

use App\DTO\Api\V1\Order\OrderPaymentDataDto;
use App\Exceptions\Payment\PaymentNotCreateException;

interface PaymentInterface
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
}
