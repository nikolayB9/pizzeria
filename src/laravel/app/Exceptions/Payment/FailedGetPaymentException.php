<?php

namespace App\Exceptions\Payment;

class FailedGetPaymentException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Ошибка при получении информации о платеже.');
    }
}
