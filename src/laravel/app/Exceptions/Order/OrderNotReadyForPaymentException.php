<?php

namespace App\Exceptions\Order;

class OrderNotReadyForPaymentException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Заказ создан, но возникла ошибка при подготовке к оплате.');
    }
}
