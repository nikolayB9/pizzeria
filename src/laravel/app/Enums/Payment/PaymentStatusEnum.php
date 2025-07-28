<?php

namespace App\Enums\Payment;

use App\Exceptions\YooKassa\InvalidYooKassaStatusException;

enum PaymentStatusEnum: int
{
    case CREATED = 1;
    case PENDING = 2;
    case WAITING_CAPTURE = 3;
    case SUCCEEDED = 4;
    case FAILED = 5;
    case CANCELLED = 6;
    case REFUNDED = 7;

    public function slug(): string
    {
        return match ($this) {
            self::CREATED => 'created',
            self::PENDING => 'pending',
            self::WAITING_CAPTURE => 'waiting_capture',
            self::SUCCEEDED => 'succeeded',
            self::FAILED => 'failed',
            self::CANCELLED => 'canceled',
            self::REFUNDED => 'refunded',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Создан',
            self::PENDING => 'Платёж ожидает завершения',
            self::WAITING_CAPTURE => 'Ожидает подтверждения',
            self::SUCCEEDED => 'Успешно оплачен',
            self::FAILED => 'Ошибка оплаты',
            self::CANCELLED => 'Платёж отменён',
            self::REFUNDED => 'Деньги возвращены',
        };
    }

    public static function fromYooKassaStatus(string $yooKassaStatus): self
    {
        return match ($yooKassaStatus) {
            'pending' => self::PENDING,
            'waiting_for_capture' => self::WAITING_CAPTURE,
            'succeeded' => self::SUCCEEDED,
            'canceled' => self::CANCELLED,
            default => throw new InvalidYooKassaStatusException(
                "Неизвестный статус от ЮKassa: $yooKassaStatus."
            ),
        };
    }
}
