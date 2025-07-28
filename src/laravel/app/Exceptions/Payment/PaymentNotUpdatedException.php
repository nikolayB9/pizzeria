<?php

namespace App\Exceptions\Payment;

class PaymentNotUpdatedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Платеж не обновлен.');
    }
}
