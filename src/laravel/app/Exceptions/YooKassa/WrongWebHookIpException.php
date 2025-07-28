<?php

namespace App\Exceptions\YooKassa;

class WrongWebHookIpException extends \Exception
{
    public function __construct()
    {
        parent::__construct('IP не разрешён.');
    }
}
