<?php

namespace App\Exceptions\Domain\Order;

use App\Exceptions\Domain\DomainException;

/**
 * Исключение выбрасывается при неудачной попытке создания заказа.
 *
 * ❌ Заказ не был создан.
 *
 * 🔒 В этом исключении `meta` всегда содержит только ['order_created' => false].
 * Параметр `$meta` убран из конструктора, чтобы исключить возможность его изменения.
 *
 * @property-read array{order_created: false} $meta
 */
class OrderCreationFailedException extends DomainException
{
    public function __construct(?string $message = null, ?int $code = null, ?array $errors = null)
    {
        parent::__construct($message, $code, $errors, static::defaultMeta());
    }

    #[\Override] protected static function defaultMessage(): string
    {
        return 'Не удалось создать заказ.';
    }

    #[\Override] protected static function defaultCode(): int
    {
        return 422;
    }

    protected static function defaultMeta(): array
    {
        return ['order_created' => false];
    }
}
