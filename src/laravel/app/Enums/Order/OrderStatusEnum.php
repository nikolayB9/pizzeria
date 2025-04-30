<?php

namespace App\Enums\Order;

enum OrderStatusEnum: int
{
    case CREATED = 1;
    case PAID = 2;
    case SHIPPED = 3;
    case IN_TRANSIT = 4;
    case DELIVERED = 5;
    case CANCELLED = 6;
    case RETURNED = 7;

    public function slug(): string
    {
        return match ($this) {
            self::CREATED => 'created',
            self::PAID => 'paid',
            self::SHIPPED => 'shipped',
            self::IN_TRANSIT => 'in_transit',
            self::DELIVERED => 'delivered',
            self::CANCELLED => 'cancelled',
            self::RETURNED => 'returned',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Создан',
            self::PAID => 'Оплачен',
            self::SHIPPED => 'Отправлен',
            self::IN_TRANSIT => 'В пути',
            self::DELIVERED => 'Получен',
            self::CANCELLED => 'Отменен',
            self::RETURNED => 'Оформлен возврат',
        };
    }
}
