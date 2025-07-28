<?php

namespace App\DTO\Api\V1\WebHook;

use App\Enums\WebHook\YooKassaWebHookEventEnum;

class WebHookPaymentDto
{
    public function __construct(
        public YooKassaWebHookEventEnum $event,
        public int                      $order_id,
        public string                   $gateway_payment_id,
        public string                   $amount,
    )
    {
    }
}
