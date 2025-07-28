<?php

namespace App\Enums\Payment;

enum PaymentGatewayEnum: int
{
    case YOOKASSA = 1;

    public function slug(): string
    {
        return match ($this) {
            self::YOOKASSA => 'yookassa',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::YOOKASSA => 'ЮKassa',
        };
    }
}
