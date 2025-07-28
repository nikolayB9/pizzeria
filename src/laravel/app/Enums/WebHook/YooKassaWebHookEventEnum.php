<?php

namespace App\Enums\WebHook;

enum YooKassaWebHookEventEnum: string
{
    case PAYMENT_WAITING_FOR_CAPTURE = 'payment.waiting_for_capture';
    case PAYMENT_SUCCEEDED = 'payment.succeeded';
    case PAYMENT_CANCELLED = 'payment.canceled';
    case REFUND_SUCCEEDED = 'refund.succeeded';

    public function label(): string
    {
        return match ($this) {
            self::PAYMENT_WAITING_FOR_CAPTURE => 'Поступление платежа, который нужно подтвердить',
            self::PAYMENT_SUCCEEDED => 'Успешный платёж',
            self::PAYMENT_CANCELLED => 'Отмена платежа или ошибка оплаты',
            self::REFUND_SUCCEEDED => 'Успешный возврат денег покупателю',
        };
    }
}
