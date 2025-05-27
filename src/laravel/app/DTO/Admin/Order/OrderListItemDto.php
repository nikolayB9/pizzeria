<?php

namespace App\DTO\Admin\Order;

use App\DTO\Traits\RequiresPreload;
use App\Enums\Order\OrderStatusEnum;
use App\Exceptions\Dto\RelationIsNullException;
use App\Exceptions\Dto\RequiredRelationMissingException;
use App\Exceptions\Order\MissingRequiredParameterInConfigException;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OrderListItemDto
{
    use RequiresPreload;

    public function __construct(
        public int             $id,
        public string          $created_at,
        public string          $delivery,
        public float           $total,
        public string          $user,
        public OrderStatusEnum $status,
    )
    {
    }

    /**
     * Создаёт DTO из модели Order.
     *
     * @param Order $order Модель Order с предзагруженным отношением user.
     *
     * @return self
     * @throws RequiredRelationMissingException Если отношение user не загружено.
     * @throws RelationIsNullException Если отношение user равно null.
     * @throws MissingRequiredParameterInConfigException Если не задан параметр slot_duration в конфиге.
     */
    public static function fromModel(Order $order): self
    {
        self::checkRequireNotNullRelations($order, 'user');

        $slotDuration = config('order.slot_duration');

        if (is_null($slotDuration)) {
            throw new MissingRequiredParameterInConfigException("Не задан slot_duration в конфиге.");
        }

        $deliveryFrom = Carbon::parse($order->delivery_at);
        $slot = $deliveryFrom->translatedFormat('H:i')
            . ' - '
            . $deliveryFrom->addMinutes($slotDuration)->translatedFormat('H:i');

        return new self(
            id: $order->id,
            created_at: $order->created_at->translatedFormat('d F Yг. H:i'),
            delivery: $slot,
            total: $order->total,
            user: $order->user->email,
            status: $order->status,
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection<Order> $orders Коллекция моделей Order.
     *
     * @return OrderListItemDto[] Массив DTO.
     * @throws RequiredRelationMissingException
     * @throws RelationIsNullException
     * @throws MissingRequiredParameterInConfigException
     */
    public static function collection(Collection $orders): array
    {
        return $orders->map(fn($order) => self::fromModel($order))->toArray();
    }
}
