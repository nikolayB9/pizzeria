<?php

namespace App\Repositories\Api\V1\Payment;

use App\DTO\Api\V1\Payment\CreatePaymentDto;
use App\Enums\Payment\PaymentStatusEnum;
use App\Exceptions\Payment\FailedCreatePaymentException;
use App\Exceptions\Payment\OrderNotFoundWhenCreatingPaymentException;
use App\Exceptions\Payment\PaymentNotFoundException;
use App\Exceptions\Payment\PaymentStatusNotUpdatedException;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function createPayment(CreatePaymentDto $dto): void
    {
        if (!Order::where('id', $dto->order_id)->where('user_id', $dto->user_id)->exists()) {
            Log::error('Не найден заказ при попытке записи данных платежа в БД', [
                'order_id' => $dto->order_id,
                'user_id' => $dto->user_id,
                'payment_id' => $dto->payment_id,
                'method' => __METHOD__,
            ]);

            throw new OrderNotFoundWhenCreatingPaymentException(
                'Не найден заказ при попытке записи данных платежа в БД.'
            );
        }

        try {
            Payment::create($dto->toInsertArray());
        } catch (\Throwable $e) {
            Log::error('Ошибка при создании платежа в БД', [
                'order_id' => $dto->order_id,
                'user_id' => $dto->user_id,
                'payment_id' => $dto->payment_id,
                'error_message' => $e->getMessage(),
                'method' => __METHOD__,
            ]);

            throw new FailedCreatePaymentException('Непредвиденная ошибка при записи платежа в БД.');
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
