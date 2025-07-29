<?php

namespace App\DTO\Api\V1\Order;

use App\DTO\Traits\RequiresPreload;
use App\Exceptions\System\Dto\PivotAttributeMissingException;
use App\Exceptions\System\Dto\PivotMissingException;
use App\Exceptions\System\Dto\RelationIsNullException;
use App\Exceptions\System\Dto\RequiredRelationMissingException;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class OrderProductListItemDto
{
    use RequiresPreload;

    public function __construct(
        public string $name,
        public string $variant_name,
        public float $price,
        public int $qty,
        public string $preview_image_url,
    ) {
    }

    /**
     * Создаёт DTO из модели ProductVariant.
     *
     * @param ProductVariant $variant Экземпляр модели продукта с предзагруженными отношениями и данными pivot таблицы.
     *
     * @return self
     * @throws RequiredRelationMissingException Если отношение product.previewImage не было загружено.
     * @throws RelationIsNullException Если загруженное отношение равно null.
     * @throws PivotMissingException Если отсутствует объект pivot.
     * @throws PivotAttributeMissingException Если атрибут price или qty отсутствует в pivot.
     */
    public static function fromModel(ProductVariant $variant): self
    {
        self::checkRequireNotNullAllRelationPaths($variant, 'product.previewImage');
        self::checkRequirePivotAttributes($variant, ['price', 'qty']);

        $product = $variant->product;

        return new self(
            name: $product->name,
            variant_name: $variant->name,
            price: $variant->pivot->price,
            qty: $variant->pivot->qty,
            preview_image_url: url(
                $product->previewImage->image_path
                    ?: config(
                    'product.default_image_path'
                ),
            )
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection<int, ProductVariant> $productVariants Коллекция моделей ProductVariant.
     *
     * @return OrderProductListItemDto[] Массив DTO.
     * @throws RequiredRelationMissingException
     * @throws RelationIsNullException
     * @throws PivotMissingException
     * @throws PivotAttributeMissingException
     */
    public static function collection(Collection $productVariants): array
    {
        return $productVariants->map(fn($variant) => self::fromModel($variant))->toArray();
    }
}
