<?php

namespace App\Services\Api\V1\Payment;

use App\DTO\Api\V1\Order\OrderPaymentDataDto;
use App\Exceptions\Payment\MissingYooKassaConfigValueException;
use App\Exceptions\Payment\PaymentNotCreateException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YooKassaService implements PaymentInterface
{
    /**
     * Создает платеж в ЮKassa для заказа и возвращает ссылку на оплату.
     *
     * @param OrderPaymentDataDto $orderData DTO с данными заказа для создания платежа.
     *
     * @return string Ссылка на оплату оформленного заказа.
     * @throws PaymentNotCreateException Если не удалось создать платеж.
     * @throws MissingYooKassaConfigValueException Если не задан один или несколько параметров конфигурации.
     */
    public function createPaymentForOrder(OrderPaymentDataDto $orderData): string
    {
        $config = $this->getYooKassaConfigData();
        $idempotenceKey = (string)Str::uuid();
        $url = $config['api_url'] . '/payments';

        try {
            $response = Http::withBasicAuth($config['shop_id'], $config['secret_key'])
                ->withHeaders([
                    'Idempotence-Key' => $idempotenceKey,
                ])
                ->post($url, [
                    'amount' => [
                        'value' => number_format($orderData->total, 2, '.', ''),
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
                ]);
        } catch (ConnectionException $e) {
            Log::error('Ошибка при запросе на создание платежа в ЮKassa', [
                'url' => $url,
                'order_id' => $orderData->id,
                'order_total' => $orderData->total,
                'idempotence_key' => $idempotenceKey,
                'error_message' => $e->getMessage(),
                'method' => __METHOD__,
            ]);

            throw new PaymentNotCreateException();
        }

        if ($response->failed()) {
            Log::error('Ошибка при создании платежа от ЮKassa', [
                'order_id' => $orderData->id,
                'order_total' => $orderData->total,
                'idempotence_key' => $idempotenceKey,
                'response' => $response->body(),
                'method' => __METHOD__,
            ]);

            throw new PaymentNotCreateException();
        }

        $data = $response->json();

        $confirmationUrl = $data['confirmation']['confirmation_url'] ?? null;

        if (!$confirmationUrl) {
            Log::error('В ответе от ЮKassa отсутствует confirmation_url', [
                'order_id' => $orderData->id,
                'order_total' => $orderData->total,
                'payment_id' => $data['id'] ?? 'неизвестно',
                'response' => $data,
                'method' => __METHOD__,
            ]);

            throw new PaymentNotCreateException();
        }

        return $confirmationUrl;
    }

    /**
     * Получение конфигурации ЮKassa из конфига Laravel.
     *
     * @return array{shop_id: string, secret_key: string, return_url:string, api_url: string } Конфигурация ЮKassa.
     * @throws MissingYooKassaConfigValueException Если не задан один или несколько параметров конфигурации.
     */
    protected function getYooKassaConfigData(): array
    {
        $shopId = config('services.yookassa.shop_id');
        $secretKey = config('services.yookassa.secret_key');
        $returnUrl = config('services.yookassa.return_url');
        $apiUrl = config('services.yookassa.api_url');

        if (!$shopId || !$secretKey || !$returnUrl || !$apiUrl) {
            Log::error('Не найден один или несколько параметров конфигурации ЮKassa', [
                'shop_id' => $shopId ?? 'Не задан',
                'secret_key' => $secretKey ?? 'Не задан',
                'return_url' => $returnUrl ?? 'Не задан',
                'api_url' => $apiUrl ?? 'Не задан',
                'method' => __METHOD__,
            ]);

            throw new MissingYooKassaConfigValueException(
                'Не заданы необходимые значения конфига ЮKassa.'
            );
        }

        return [
            'shop_id' => $shopId,
            'secret_key' => $secretKey,
            'return_url' => $returnUrl,
            'api_url' => $apiUrl,
        ];
    }
}
