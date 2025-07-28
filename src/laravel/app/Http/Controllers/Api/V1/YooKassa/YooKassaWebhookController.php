<?php

namespace App\Http\Controllers\Api\V1\YooKassa;

use App\Exceptions\YooKassa\FailedWebHookHandleException;
use App\Exceptions\YooKassa\WrongWebHookDataException;
use App\Exceptions\YooKassa\WrongWebHookIpException;
use App\Http\Controllers\Controller;
use App\Services\Api\V1\YooKassaWebhookService;
use Illuminate\Http\Request;

class YooKassaWebhookController extends Controller
{
    public function __construct(private readonly YooKassaWebhookService $webhookService)
    {
    }

    public function handle(Request $request)
    {
        try {
            $validated = $this->webhookService->validateWebHook($request);
        } catch (WrongWebHookIpException) {
            return response()->json(['message' => 'Forbidden'], 403);
        } catch (WrongWebHookDataException) {
            return response()->json(['message' => 'Bad Request'], 400);
        }

        try {
            match ($validated->event->label()) {
                //'payment.waiting_for_capture' => $this->webhookService->handlePaymentWaitingForCapture($payload),
                'payment.succeeded' => $this->webhookService->handlePaymentSucceeded($validated),
                //'payment.canceled' => $this->webhookService->handlePaymentCanceled($payload),
                //'refund.succeeded' => $this->webhookService->handleRefundSucceeded($payload),
            };
        } catch (FailedWebHookHandleException) {

        }

        return response()->json(['status' => 'ok']);
    }
}
