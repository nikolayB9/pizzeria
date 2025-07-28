<?php

namespace App\Exceptions\YooKassa;

class WrongWebHookDataException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Обязательные поля отсутствуют или неверны.');
    }
}
