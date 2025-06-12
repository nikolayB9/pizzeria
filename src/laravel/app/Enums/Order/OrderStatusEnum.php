<?php

namespace App\Enums\Order;

enum OrderStatusEnum: int
{
    case CREATED = 1;
    case WAITING_PAYMENT = 2;
    case PAID = 3;
    case PREPARING = 4;
    case IN_TRANSIT = 5;
    case DELIVERED = 6;
    case CANCELLED = 7;

    public function slug(): string
    {
        return match ($this) {
            self::CREATED => 'created',
            self::WAITING_PAYMENT => 'waiting_payment',
            self::PAID => 'paid',
            self::PREPARING => 'preparing',
            self::IN_TRANSIT => 'in_transit',
            self::DELIVERED => 'delivered',
            self::CANCELLED => 'cancelled',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Создан',
            self::WAITING_PAYMENT => 'Ждет оплаты',
            self::PAID => 'Оплачен',
            self::PREPARING => 'Готовится',
            self::IN_TRANSIT => 'В пути',
            self::DELIVERED => 'Получен',
            self::CANCELLED => 'Отменен',
        };
    }
}
