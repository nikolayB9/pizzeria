<?php

namespace App\Exceptions\Domain\Payment;

use App\Exceptions\Domain\DomainException;

/**
 * Исключение выбрасывается при неудачной попытке применить ответ платёжного шлюза к платежу.
 *
 * 🔒 В этом исключении `meta` всегда содержит только ['order_created' => true].
 * Параметр `$meta` убран из конструктора, чтобы исключить возможность его изменения.
 *
 * @property-read array{order_created: true} $meta
 */
class PaymentGatewayResponseApplyFailedException extends DomainException
{
    public function __construct(?string $message = null, ?int $code = null, ?array $errors = null)
    {
        parent::__construct($message, $code, $errors, static::defaultMeta());
    }

    #[\Override] protected static function defaultMessage(): string
    {
        return 'Ошибка платежа. Пожалуйста, попробуйте оплатить заказ еще раз.';
    }

    #[\Override] protected static function defaultCode(): int
    {
        return 500;
    }

    protected static function defaultMeta(): array
    {
        return ['order_created' => true];
    }
}
