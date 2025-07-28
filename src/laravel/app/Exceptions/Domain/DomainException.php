<?php

namespace App\Exceptions\Domain;

use Exception;

/**
 * Базовый класс для всех доменных исключений.
 *
 * Используется для ошибок бизнес-логики (домена), которые необходимо
 * корректно показать клиенту (например, пустая корзина, некорректное время доставки).
 *
 * ❗ Важно:
 * - От этого класса должны наследоваться все исключения,
 *   которые относятся к предметной области (Domain Layer).
 * - Каждое наследуемое исключение обязано определить методы
 *   `defaultMessage()` и `defaultCode()`, чтобы всегда было
 *   корректное сообщение и HTTP-код ответа.
 * - Исключение может содержать дополнительные данные:
 *   `errors` (детали ошибки) и `meta` (сопутствующие данные).
 *
 * @property-read array $errors Детализированные ошибки для ответа клиенту
 * @property-read array $meta   Дополнительные данные для ответа клиенту
 */
abstract class DomainException extends Exception
{
    protected array $errors = [];
    protected array $meta = [];

    public function __construct(
        ?string $message = null,
        ?int $code = null,
        array $errors = [],
        array $meta = [],
    ) {
        parent::__construct(
            $message ?? static::defaultMessage(),
            $code ?? static::defaultCode()
        );

        $this->errors = $errors;
        $this->meta = $meta;
    }

    abstract protected static function defaultMessage(): string;

    abstract protected static function defaultCode(): int;

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }
}
