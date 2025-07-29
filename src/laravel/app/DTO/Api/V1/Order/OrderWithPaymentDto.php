<?php

namespace App\DTO\Api\V1\Order;

use App\DTO\Traits\RequiresPreload;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Exceptions\System\Dto\RelationIsNullException;
use App\Exceptions\System\Dto\RequiredRelationMissingException;
use App\Models\Order;

class OrderWithPaymentDto
{
    use RequiresPreload;

    public function __construct(
        public int $order_id,
        public OrderStatusEnum $order_status,
        public string $order_total,
        public int $payment_id,
        public string $gateway_payment_id,
        public PaymentStatusEnum $payment_status,
        public string $payment_amount,
    ) {
    }

    /**
     * Создаёт DTO из модели Order.
     *
     * @param Order $order Модель Order с предзагруженным отношением payment.
     *
     * @return self
     * @throws RequiredRelationMissingException Если отношение payment не загружено.
     * @throws RelationIsNullException Если загруженное отношение payment равно null.
     */
    public static function fromModel(Order $order): self
    {
        self::checkRequireNotNullRelations($order, 'payment');

        $payment = $order->payment;

        return new self(
            order_id: $order->id,
            order_status: $order->status,
            order_total: $order->total,
            payment_id: $payment->id,
            gateway_payment_id: $payment->gateway_payment_id,
            payment_status: $payment->status,
            payment_amount: $payment->amount,
        );
    }
}
