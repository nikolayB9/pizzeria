<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\V1\Payment\YooKassaService;

class PaymentController extends Controller
{
    public function __construct(private readonly YooKassaService $yooKassaService)
    {
    }

    public function pay(int $orderId)
    {
        $amount = 999; // временно: тестовая сумма. Можно заменить на сумму из заказа

        $confirmationUrl = $this->yooKassaService->createPayment($amount, (string)$orderId);

        return redirect($confirmationUrl);
    }
}
