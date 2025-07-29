<?php

namespace App\Exceptions\Domain;

use Exception;

/**
 * Базовый класс для всех доменных (бизнес-) исключений.
 *
 * Используется для ошибок, которые должны быть возвращены клиенту в формате API.
 * В отличие от системных ошибок, эти исключения описывают предсказуемые ситуации
 * бизнес-логики (например, не удалось создать заказ, корзина пуста и т.д.).
 *
 * 📌 Особенности:
 * - Наследники обязаны определить сообщение об ошибке и код ответа через
 *   {@see defaultMessage()} и {@see defaultCode()}.
 * - Можно задать дополнительные данные через `errors` и `meta`.
 * - Для строгих исключений, где meta фиксированное, параметр `$meta` убирается
 *   из конструктора наследника — тогда изменить его извне невозможно.
 *
 * @property-read array $errors Список детальных ошибок (например, поля формы)
 * @property-read array $meta   Доп. информация для фронтенда (например, ['order_created' => false])
 */
abstract class DomainException extends Exception
{
    protected array $errors = [];
    protected array $meta = [];

    public function __construct(
        ?string $message = null,
        ?int $code = null,
        ?array $errors = null,
        ?array $meta = null,
    ) {
        parent::__construct(
            $message ?? static::defaultMessage(),
            $code ?? static::defaultCode()
        );

        $this->errors = $errors ?? static::defaultErrors();
        $this->meta = $meta ?? static::defaultMeta();
    }

    abstract protected static function defaultMessage(): string;

    abstract protected static function defaultCode(): int;

    protected static function defaultErrors(): array
    {
        return [];
    }

    protected static function defaultMeta(): array
    {
        return [];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }
}
