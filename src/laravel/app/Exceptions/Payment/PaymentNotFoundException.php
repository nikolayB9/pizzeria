<?php

namespace App\Exceptions\Payment;

class PaymentNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Платеж не найден.');
    }
}
