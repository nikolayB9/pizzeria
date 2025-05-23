<?php

namespace App\DTO\Api\V1\Order;

use App\DTO\Api\V1\Address\AddressShortDto;
use App\DTO\Traits\RequiresPreload;
use App\Exceptions\Dto\RelationIsNullException;
use App\Exceptions\Dto\RequiredRelationMissingException;
use App\Models\Order;
use Illuminate\Support\Collection;

class OrderListItemDto
{
    use RequiresPreload;

    /** @param OrderProductPreviewDto[] $product_previews */
    public function __construct(
        public string          $created_at,
        public AddressShortDto $address,
        public float           $total,
        public string          $status,
        public array           $product_previews,
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
            created_at: $order->created_at->translatedFormat('d F Yг. H:i'),
            address: AddressShortDto::fromModel($order->address),
            total: $order->total,
            status: $order->status->label(),
            product_previews: OrderProductPreviewDto::collection($order->products),
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection<int, Order> $orders Коллекция моделей Order.
     *
     * @return OrderListItemDto[] Массив DTO.
     * @throws RequiredRelationMissingException
     * @throws RelationIsNullException
     */
    public static function collection(Collection $orders): array
    {
        return $orders->map(fn($order) => self::fromModel($order))->toArray();
    }
}
