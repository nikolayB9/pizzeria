<?php

namespace App\DTO\Api\V1\Order;

use App\DTO\Api\V1\Cart\CartRawItemDto;
use App\Enums\Order\OrderStatusEnum;
use DateTimeImmutable;

class CreateOrderDto
{
    /**
     * @param CartRawItemDto[] $cart
     */
    public function __construct(
        public int               $user_id,
        public int               $address_id,
        public float             $delivery_cost,
        public float             $total,
        public OrderStatusEnum   $status,
        public DateTimeImmutable $delivery_at,
        public ?string           $comment,
        public array             $cart,
    )
    {
    }

    /**
     * Преобразует DTO в массив для вставки в БД.
     *
     * @return array<string, mixed> Массив для вставки в БД.
     */
    public function toInsertArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'address_id' => $this->address_id,
            'delivery_cost' => $this->delivery_cost,
            'total' => $this->total,
            'status' => $this->status,
            'delivery_at' => $this->delivery_at,
            'comment' => $this->comment,
        ];
    }
}
