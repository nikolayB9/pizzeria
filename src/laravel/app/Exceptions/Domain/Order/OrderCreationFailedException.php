<?php

namespace App\Exceptions\Domain\Order;

use App\Exceptions\Domain\DomainException;

class OrderCreationFailedException extends DomainException
{
    #[\Override] protected static function defaultMessage(): string
    {
        return 'Не удалось создать заказ.';
    }

    #[\Override] protected static function defaultCode(): int
    {
        return 500;
    }
}
