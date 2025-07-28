<?php

namespace App\Exceptions\YooKassa;

class FailedWebHookHandleException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Ошибка при обработке WebHook от ЮKassa.');
    }
}
