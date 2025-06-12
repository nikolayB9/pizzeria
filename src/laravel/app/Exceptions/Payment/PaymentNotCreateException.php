<?php

namespace App\Exceptions\Payment;

class PaymentNotCreateException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Произошла ошибка при создании платежа. Пожалуйста, попробуйте позже.');
    }
}
