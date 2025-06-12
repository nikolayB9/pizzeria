<?php

namespace App\Exceptions\Order;

class OrderNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Заказ не найден.');
    }
}
