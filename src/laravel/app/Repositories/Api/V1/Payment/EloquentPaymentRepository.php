<?php

namespace App\Repositories\Api\V1\Payment;

use App\DTO\Api\V1\Payment\CreatePaymentDto;
use App\DTO\Api\V1\Payment\InitiatePaymentDto;
use App\DTO\Api\V1\Payment\MinifiedPaymentDataDto;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Exceptions\Domain\Payment\PaymentCreationFailedException;
use App\Exceptions\Domain\Payment\PaymentGatewayResponseApplyFailedException;
use App\Exceptions\Payment\PaymentNotFoundException;
use App\Exceptions\Payment\PaymentStatusNotUpdatedException;
use App\Models\Order;
use App\Models\Payment;
use App\Repositories\Api\V1\Order\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function __construct(private readonly OrderRepositoryInterface $orderRepository)
    {
    }

    /**
     * @throws PaymentCreationFailedException
     */
    public function createPayment(CreatePaymentDto $dto): MinifiedPaymentDataDto
    {
        if (!Order::where('id', $dto->order_id)->exists()) {
            Log::error('Не найден заказ при попытке создания его платежа', [
                'order_id' => $dto->order_id,
                'amount' => $dto->amount,
                'idempotence_key' => $dto->idempotence_key,
                'method' => __METHOD__,
            ]);

            throw new PaymentCreationFailedException();
        }

        try {
            return DB::transaction(function () use ($dto) {
                $payment = Payment::create($dto->toInsertArray());

                $this->orderRepository->updateStatus($dto->order_id, OrderStatusEnum::WAITING_PAYMENT);

                return new MinifiedPaymentDataDto(
                    payment_id: $payment->id,
                    order_id: $payment->order_id,
                    status: $payment->status,
                    amount: $payment->amount,
                    idempotence_key: $payment->idempotence_key
                );
            });
        } catch (\Throwable $e) {
            Log::error('Ошибка при создании платежа в БД или обновлении статуса заказа', [
                'order_id' => $dto->order_id,
                'amount' => $dto->amount,
                'idempotence_key' => $dto->idempotence_key,
                'exception' => $e->getMessage(),
                'method' => __METHOD__,
            ]);

            throw new PaymentCreationFailedException();
        }
    }

    /**
     * @throws PaymentGatewayResponseApplyFailedException
     */
    public function applyGatewayResponse(int $paymentId, InitiatePaymentDto $dto, PaymentStatusEnum $status): void
    {
        $payment = Payment::where('id', $paymentId)->first();

        if (!$payment) {
            Log::error('Не найден платеж при попытке обновить его данные после инициализации платежа', [
                'payment_id' => $paymentId,
                'method' => __METHOD__,
            ]);

            throw new PaymentGatewayResponseApplyFailedException();
        }

        try {
            $payment->update([
                'gateway_payment_id' => $dto->gateway_payment_id,
                'status' => $status,
                'gateway' => $dto->gateway,
                'metadata' => $dto->metadata,
                'raw_response' => $dto->raw,
            ]);
        } catch (\Throwable $e) {
            Log::error('Ошибка при обновлении платежа в БД', [
                'payment_id' => $paymentId,
                'exception' => $e->getMessage(),
                'method' => __METHOD__,
            ]);

            throw new PaymentGatewayResponseApplyFailedException();
        }
    }

    /**
     * Проверяет существование платежа с заданными параметрами.
     *
     * @param array<string, mixed> $searchFields Массив с полями и их значениями для поиска (например ['id' => 123]).
     *
     * @return bool True, если платеж существует, иначе - false.
     */
    public function exists(array $searchFields): bool
    {
        return Payment::where($searchFields)->exists();
    }

    /**
     * Изменяет статус платежа.
     *
     * @param int $paymentId ID заказа.
     * @param PaymentStatusEnum $newStatus Новый статус платежа.
     *
     * @return void
     * @throws PaymentNotFoundException Если платеж не найден.
     * @throws PaymentStatusNotUpdatedException Если произошла ошибка при изменении статуса.
     */
    public function changePaymentStatus(int $paymentId, PaymentStatusEnum $newStatus): void
    {
        try {
            $payment = Payment::where('id', $paymentId)
                ->firstOrFail();

            $payment->update([
                'status' => $newStatus,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('Не найден платеж при попытке изменить его статус', [
                'payment_id' => $paymentId,
                'new_status' => $newStatus,
                'error_message' => $e->getMessage(),
                'method' => __METHOD__,
            ]);

            throw new PaymentNotFoundException();
        } catch (\Throwable $e) {
            Log::error('Не удалось изменить статус платежа', [
                'payment_id' => $paymentId,
                'new_status' => $newStatus,
                'error_message' => $e->getMessage(),
                'method' => __METHOD__,
            ]);

            throw new PaymentStatusNotUpdatedException('Непредвиденная ошибка при изменении статуса платежа.');
        }
    }
}
