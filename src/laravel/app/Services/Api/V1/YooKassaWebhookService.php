<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\WebHook\WebHookPaymentDto;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Enums\WebHook\YooKassaWebHookEventEnum;
use App\Exceptions\YooKassa\FailedWebHookHandleException;
use App\Exceptions\YooKassa\MissingYooKassaConfigValueException;
use App\Exceptions\YooKassa\WrongWebHookDataException;
use App\Exceptions\YooKassa\WrongWebHookIpException;
use App\Repositories\Api\V1\Order\OrderRepositoryInterface;
use App\Repositories\Api\V1\Payment\PaymentRepositoryInterface;
use App\Services\Api\V1\Gateway\PaymentGatewayInterface;
use App\Services\Traits\ArrayValidationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\IpUtils;

class YooKassaWebhookService
{
    use ArrayValidationTrait;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly PaymentGatewayInterface $paymentGateway
    ) {
    }

    public function handlePaymentSucceeded(WebHookPaymentDto $webHookPaymentDto)
    {
        //TODO заказ и платеж связаны - надо это проверить при валидации
        //TODO получить отдельно статус заказа, статус платежа
        //TODO проверить статусы в OrderService?
        $order = $this->orderRepository->getOrderWithPayment($webHookPaymentDto->order_id);

        $this->checkStatuses(
            $order->order_status,
            OrderStatusEnum::WAITING_PAYMENT,
            $order->payment_status,
            PaymentStatusEnum::WAITING_CAPTURE,
        );

        $payment = $this->paymentGateway->getPayment($webHookPaymentDto->gateway_payment_id);

        if ($payment->status === PaymentStatusEnum::SUCCEEDED) {
            $this->orderRepository->changeOrderStatus($order->order_id, OrderStatusEnum::PAID);
            $this->paymentRepository->changePaymentStatus($order->payment_id, PaymentStatusEnum::SUCCEEDED);
        }
    }

    /**
     * Валидирует данные переданного WebHook.
     *
     * @param Request $request Данные переданного WebHook.
     *
     * @return WebHookPaymentDto Валидированные данные переданного WebHook.
     * @throws WrongWebHookDataException Если не хватает данных или данные неверные в запросе ЮKassa.
     * @throws WrongWebHookIpException Если запрос пришел не с разрешенного IP.
     */
    public function validateWebHook(Request $request): WebHookPaymentDto
    {
        $this->validateIp($request);

        $payload = $request->all();

        $this->validatePayload($payload);

        $webHookData = new WebHookPaymentDto(
            event: YooKassaWebHookEventEnum::tryFrom($payload['event']),
            order_id: (int)$payload['object']['metadata']['order_id'],
            gateway_payment_id: (string)$payload['object']['id'],
            amount: number_format((float)$payload['object']['amount']['value'], 2, '.', ''),
        );

        $this->checkOrderAndPayment($webHookData);

        return $webHookData;
    }

    /**
     * Сверяет IP переданного WebHook с белым списком ЮKassa.
     *
     * @param Request $request Запрос, у которого проверяется IP.
     *
     * @return void
     * @throws MissingYooKassaConfigValueException Если список разрешенных IP не прописан в конфиге.
     * @throws WrongWebHookIpException Если IP не входит в белый список.
     */
    private function validateIp(Request $request): void
    {
        $ip = $request->ip();
        $allowedIps = config('services.yookassa.allowed_ips', []);

        if (!is_array($allowedIps) || empty($allowedIps)) {
            Log::error('В конфиге не заданы разрешенные IP для WebHook от ЮKassa', [
                'allowed_ips' => $allowedIps,
                'method' => __METHOD__,
            ]);

            throw new MissingYooKassaConfigValueException(
                'В конфиге не заданы разрешенные IP для WebHook от ЮKassa'
            );
        }

        if (!IpUtils::checkIp($ip, $allowedIps)) {
            Log::error('Запрос от ЮKassa отклонён: IP не входит в белый список для WebHook', [
                'request_ip' => $ip,
                'allowed_ips' => $allowedIps,
                'payload' => $request->all(),
                'method' => __METHOD__,
            ]);

            throw new WrongWebHookIpException();
        }
    }

    /**
     * Валидирует данные в WebHook от ЮKassa.
     *
     * @param array $payload Данные запроса WebHook от ЮKassa.
     *
     * @return void
     * @throws WrongWebHookDataException Если структура или значения WebHook-запроса некорректны.
     */
    private function validatePayload(array $payload): void
    {
        $errors = $this->validateRequiredKeysAndValues(
            $payload,
            ['event', 'object.id', 'object.metadata.order_id', 'object.amount.value'],
        );

        $event = $payload['event'] ?? null;

        if (!$event || is_null(YooKassaWebHookEventEnum::tryFrom($event))) {
            $errors[] = "Переданное в WebHook событие [$event] не определено.";
        }

        if (!empty($errors)) {
            Log::error('Найдены ошибки в WebHook от ЮKassa', [
                'errors' => $errors,
                'event' => $event,
                'order_id' => $payload['object']['metadata']['order_id'] ?? null,
                'gateway_payment_id' => $payload['object']['id'] ?? null,
                'amount' => $payload['object']['amount']['value'] ?? null,
                'payload' => $payload,
                'method' => __METHOD__,
            ]);

            throw new WrongWebHookDataException();
        }
    }

    /**
     * Проверка на существование заказа и его платежа по переданным в WebHook данным.
     *
     * @param WebHookPaymentDto $dto Данные WebHook от ЮKassa.
     *
     * @return void
     * @throws WrongWebHookDataException Если заказ или платеж не найдены.
     */
    private function checkOrderAndPayment(WebHookPaymentDto $dto): void
    {
        $orderExists = $this->orderRepository->exists([
            'id' => $dto->order_id,
            'total' => $dto->amount,
        ]);

        $paymentExists = $this->paymentRepository->exists([
            'gateway_payment_id' => $dto->gateway_payment_id,
            'order_id' => $dto->order_id,
            'amount' => $dto->amount,
        ]);

        if (!$orderExists || !$paymentExists) {
            Log::error('Не найден заказ или платеж при получении WebHook от ЮKassa', [
                'order_exists' => $orderExists,
                'payment_exists' => $paymentExists,
                'event' => $dto->event,
                'order_id' => $dto->order_id,
                'gateway_payment_id' => $dto->gateway_payment_id,
                'amount' => $dto->amount,
                'method' => __METHOD__,
            ]);

            throw new WrongWebHookDataException();
        }
    }

    private function checkStatuses(
        OrderStatusEnum $orderStatus,
        OrderStatusEnum $expectedOrderStatus,
        PaymentStatusEnum $paymentStatus,
        PaymentStatusEnum $expectedPaymentStatus
    ) {
        if ($orderStatus !== $expectedOrderStatus) {
            Log::error('Статус заказа не соответствует ожидаемому', [
                'order_status' => $orderStatus->name,
                'expected_status' => $expectedOrderStatus->name,
                'method' => __METHOD__,
            ]);

            throw new FailedWebHookHandleException();
        }

        if ($paymentStatus !== $expectedPaymentStatus) {
            Log::error('Статус платежа не соответствует ожидаемому', [
                'payment_status' => $paymentStatus->name,
                'expected_status' => $expectedPaymentStatus->name,
                'method' => __METHOD__,
            ]);

            throw new FailedWebHookHandleException();
        }
    }
}
