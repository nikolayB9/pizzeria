<?php

namespace App\DTO\Api\V1\Order;

use App\DTO\Api\V1\Address\AddressShortDto;
use App\DTO\Traits\RequiresPreload;
use App\Exceptions\Dto\RelationIsNullException;
use App\Exceptions\Dto\RequiredRelationMissingException;
use App\Models\Order;

class OrderDto
{
    use RequiresPreload;

    /** @param OrderProductListItemDto[] $products */
    public function __construct(
        public int             $id,
        public array           $products,
        public float           $total,
        public float           $delivery_cost,
        public AddressShortDto $address,
        public string          $created_at,
        public string          $status,
    )
    {
    }

    /**
     * Создаёт DTO из модели Order.
     *
     * @param Order $order Модель Order с предзагруженными отношениями address, products.product.previewImage.
     *
     * @return self
     * @throws RequiredRelationMissingException Если одно из указанных отношений не загружено.
     * @throws RelationIsNullException Если загруженное отношение равно null.
     */
    public static function fromModel(Order $order): self
    {
        self::checkRequireNotNullAllRelationPaths($order, ['address', 'products.product.previewImage']);

        return new self(
            id: $order->id,
            products: OrderProductListItemDto::collection($order->products),
            total: $order->total,
            delivery_cost: $order->delivery_cost,
            address: AddressShortDto::fromModel($order->address),
            created_at: $order->created_at->translatedFormat('d F Yг. H:i'),
            status: $order->status->label(),
        );
    }
}
