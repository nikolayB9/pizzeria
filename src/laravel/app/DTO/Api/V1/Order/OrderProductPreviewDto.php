<?php

namespace App\DTO\Api\V1\Order;

use App\DTO\Traits\RequiresPreload;
use App\Exceptions\System\Dto\RelationIsNullException;
use App\Exceptions\System\Dto\RequiredRelationMissingException;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class OrderProductPreviewDto
{
    use RequiresPreload;

    public function __construct(
        public string $url,
    ) {
    }


    /**
     * Создаёт DTO из модели ProductVariant.
     *
     * @param ProductVariant $productVariant Экземпляр ProductVariant с предзагруженным отношением product.previewImage.
     *
     * @return self
     * @throws RequiredRelationMissingException Если не загружено отношение product или product.previewImage.
     * @throws RelationIsNullException Если загруженное отношение равно null.
     */
    public static function fromModel(ProductVariant $productVariant): self
    {
        self::checkRequireNotNullAllRelationPaths($productVariant, 'product.previewImage');

        return new self(
            url: url(
                $productVariant->product->previewImage->image_path
                    ?: config('product.default_image_path')
            ),
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection<int, ProductVariant> $productVariants Коллекция моделей ProductVariant.
     *
     * @return OrderProductPreviewDto[] Массив DTO.
     * @throws RequiredRelationMissingException
     * @throws RelationIsNullException
     */
    public static function collection(Collection $productVariants): array
    {
        return $productVariants->map(fn($variant) => self::fromModel($variant))->toArray();
    }
}
