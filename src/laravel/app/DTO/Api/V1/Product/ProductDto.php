<?php

namespace App\DTO\Api\V1\Product;

use App\DTO\Traits\RequiresPreload;
use App\Exceptions\System\Dto\RelationIsNotCollectionException;
use App\Exceptions\System\Dto\RelationIsNullException;
use App\Exceptions\System\Dto\RequiredRelationMissingException;
use App\Models\Product;

class ProductDto
{
    use RequiresPreload;

    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public string $detail_image_url,
        public array $variants,
    ) {
    }

    /**
     * Создаёт DTO из модели Product.
     *
     * @param Product $product Экземпляр модели продукта с предзагруженными отношениями detailImage, variants.
     *
     * @return self
     * @throws RequiredRelationMissingException Если detailImage или variants не были загружены.
     * @throws RelationIsNullException Если detailImage или variants равны null.
     * @throws RelationIsNotCollectionException Если variants не является экземпляром Illuminate\Support\Collection.
     */
    public static function fromModel(Product $product): self
    {
        self::checkRequireNotNullRelations($product, 'detailImage');
        self::checkRequireCollectionInRelations($product, 'variants');

        return new self(
            id: $product->id,
            name: $product->name,
            description: $product->description,
            detail_image_url: url($product->detailImage->image_path ?: config('product.default_image_path')),
            variants: VariantDto::collection($product->variants),
        );
    }
}
