<?php

namespace App\Services\Api\V1\Gateway;

use App\DTO\Api\V1\Payment\InitiatePaymentDto;
use App\DTO\Api\V1\Payment\MinifiedPaymentDataDto;
use App\DTO\Api\V1\Payment\PaymentDto;
use App\Enums\Payment\PaymentGatewayEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Exceptions\Payment\FailedGetPaymentException;
use App\Exceptions\Payment\InitiatePaymentFailedException;
use App\Exceptions\YooKassa\FailedGetPaymentFromYooKassaException;
use App\Exceptions\YooKassa\FailedYooKassaResponseException;
use App\Exceptions\YooKassa\InvalidYooKassaStatusException;
use App\Exceptions\YooKassa\MissingYooKassaConfigValueException;
use App\Exceptions\YooKassa\YooKassaConnectionException;
use App\Repositories\Api\V1\Payment\PaymentRepositoryInterface;
use App\Services\Traits\ArrayValidationTrait;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YooKassaGatewayService implements PaymentGatewayInterface
{
    use ArrayValidationTrait;

    /** @var array{shop_id: string, secret_key: string, return_url: string, api_url: string, statuses: array}|null */
    private ?array $config = null;

    public function __construct(private readonly PaymentRepositoryInterface $paymentRepository)
    {
    }

    /**
     * Создает платеж в ЮKassa для заказа и возвращает ссылку на оплату.
     *
     * @throws MissingYooKassaConfigValueException Если не задан один или несколько параметров конфигурации.
     * @throws InitiatePaymentFailedException
     */
    public function initiatePayment(MinifiedPaymentDataDto $dto): InitiatePaymentDto
    {
        $config = $this->getYooKassaConfigData();

        $postData = [
            'amount' => [
                'value' => number_format($dto->amount, 2, '.', ''),
                'currency' => 'RUB',
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => $config['return_url'],
            ],
            'capture' => true,
            'description' => 'Тестовая оплата',
            'payment_method_data' => [
                'type' => 'bank_card',
            ],
            'metadata' => [
                'order_id' => (string)$dto->order_id,
            ],
        ];

        try {
            $response = $this->sendYooKassaRequest(
                $config['api_url'] . '/payments',
                'post',
                ['Idempotence-Key' => $dto->idempotence_key],
                $postData,
            );
        } catch (YooKassaConnectionException|FailedYooKassaResponseException $e) {
            Log::error('Не удалось создать платеж для заказа при запросе в ЮKassa', [
                'payment_data' => $dto,
                'error_message' => $e->getMessage(),
                'method' => __METHOD__,
            ]);

            throw new InitiatePaymentFailedException();
        }

        $data = $response->json();

        $responseErrors = $this->validateRequiredKeysAndValues(
            $data,
            ['id', 'confirmation.confirmation_url', 'metadata.order_id'],
            ['metadata.order_id' => $dto->order_id],
        );

        if (!empty($responseErrors)) {
            Log::error('Ошибки в данных ответа от ЮKassa при создании платежа', [
                'errors' => $responseErrors,
                'response' => $data,
                'order_id' => $dto->order_id,
                'method' => __METHOD__,
            ]);
            throw new InitiatePaymentFailedException();
        }

        return new InitiatePaymentDto(
            gateway_payment_id: $data['id'],
            confirmation_url: $data['confirmation']['confirmation_url'],
            metadata: $data['metadata'],
            raw: $data,
            gateway: PaymentGatewayEnum::YOOKASSA,
        );
    }

    public function getPayment(string $gatewayPaymentId): PaymentDto
    {
        $config = $this->getYooKassaConfigData();

        try {
            $response = $this->sendYooKassaRequest(
                $config['api_url'] . "/payments/$gatewayPaymentId",
                'get',
            );
        } catch (YooKassaConnectionException|FailedYooKassaResponseException $e) {
            Log::error('Не удалось получить информацию о платеже при запросе в ЮKassa', [
                'gateway_payment_id' => $gatewayPaymentId,
                'error_message' => $e->getMessage(),
                'method' => __METHOD__,
            ]);

            throw new FailedGetPaymentException();
        }

        $payment = $response->json();

        $this->validatePaymentGettingResponse($payment);

        try {
            $status = PaymentStatusEnum::fromYooKassaStatus($payment['status']);
        } catch (InvalidYooKassaStatusException $e) {
            Log::error('Неизвестный статус при получении платежа от ЮKassa', [
                'error_message' => $e->getMessage(),
                'status' => $payment['status'],
                'method' => __METHOD__,
            ]);

            throw new FailedGetPaymentFromYooKassaException();
        }

        return new PaymentDto(
            gateway_payment_id: $payment['id'],
            order_id: $payment['metadata']['order_id'],
            status: $status,
            amount: $payment['amount']['value'],
        );
    }

    /**
     * Отправляет запрос в ЮKassa и возвращает ответ.
     *
     * @param string $url URL запроса.
     * @param string $method Http-метод запроса (например get, post).
     * @param array $headers Заголовки запроса.
     * @param array $data Тело запроса.
     *
     * @return Response Ответ от ЮKassa.
     * @throws YooKassaConnectionException Если не удалось установить соединение с ЮKassa.
     * @throws FailedYooKassaResponseException Если ЮKassa вернула ошибку в ответе.
     */
    private function sendYooKassaRequest(string $url, string $method, array $headers = [], array $data = []): Response
    {
        $config = $this->config ?? $this->getYooKassaConfigData();

        try {
            $response = Http::withBasicAuth($config['shop_id'], $config['secret_key'])
                ->withHeaders($headers)
                ->send($method, $url, ['json' => $data]);
        } catch (ConnectionException|\Exception $e) {
            Log::error('Не удалось установить соединение с ЮKassa', [
                'url' => $url,
                'http_method' => $method,
                'headers' => $headers,
                'data' => $data,
                'error_message' => $e->getMessage(),
                'method' => __METHOD__,
            ]);

            throw new YooKassaConnectionException('Не удалось установить соединение с ЮKassa.');
        }

        if ($response->failed()) {
            Log::error('ЮKassa вернула ошибку в ответе', [
                'url' => $url,
                'http_method' => $method,
                'headers' => $headers,
                'data' => $data,
                'response' => $response->body(),
                'status' => $response->status(),
                'method' => __METHOD__,
            ]);

            throw new FailedYooKassaResponseException('ЮKassa вернула ошибку в ответе.');
        }

        return $response;
    }

    /**
     * Валидирует данные ответа ЮKassa на запрос получения платежа.
     *
     * @param array $data Данные ответа ЮKassa.
     *
     * @return void
     * @throws FailedGetPaymentFromYooKassaException Если в ответе содержится критическая ошибка.
     */
    private function validatePaymentGettingResponse(array $data): void
    {
        $responseErrors = $this->validateRequiredKeysAndValues(
            $data,
            ['id', 'status', 'amount.value', 'metadata.order_id'],
        );

        if (!empty($responseErrors)) {
            Log::error('Не хватает данных в ответе ЮKassa при получении платежа', [
                'errors' => $responseErrors,
                'response' => $data,
                'method' => __METHOD__,
            ]);

            throw new FailedGetPaymentFromYooKassaException();
        }
    }

    /**
     * Получение конфигурации ЮKassa из конфига Laravel.
     *
     * @return array{shop_id: string, secret_key: string, return_url: string, api_url: string } Конфигурация ЮKassa.
     * @throws MissingYooKassaConfigValueException Если не задан один или несколько параметров конфигурации.
     */
    private function getYooKassaConfigData(): array
    {
        if ($this->config) {
            return $this->config;
        }

        $config = config('services.yookassa');

        $shopId = $config['shop_id'] ?? null;
        $secretKey = $config['secret_key'] ?? null;
        $returnUrl = $config['return_url'] ?? null;
        $apiUrl = $config['api_url'] ?? null;
        $statuses = $config['statuses'] ?? null;

        if (!$config || !$shopId || !$secretKey || !$returnUrl || !$apiUrl || !is_array($statuses)) {
            Log::error('Не найден один или несколько параметров конфигурации ЮKassa', [
                'shop_id' => $shopId ?? 'Не задан',
                'secret_key' => $secretKey ?? 'Не задан',
                'return_url' => $returnUrl ?? 'Не задан',
                'api_url' => $apiUrl ?? 'Не задан',
                'statuses' => $statuses ?? 'Не заданы',
                'method' => __METHOD__,
            ]);

            throw new MissingYooKassaConfigValueException(
                'Не заданы необходимые значения конфига ЮKassa.'
            );
        }

        $this->config = [
            'shop_id' => $shopId,
            'secret_key' => $secretKey,
            'return_url' => $returnUrl,
            'api_url' => $apiUrl,
            'statuses' => $statuses,
        ];

        return $this->config;
    }
}
