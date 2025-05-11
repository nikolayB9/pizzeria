<?php

namespace App\DTO\Api\V1\Cart;

use App\DTO\Traits\RequiresPreload;
use App\Exceptions\Dto\RelationIsNullException;
use App\Exceptions\Dto\RequiredRelationMissingException;
use App\Exceptions\Product\MissingProductCategoryException;
use App\Models\ProductVariant;

class AddToCartProductDto
{
    use RequiresPreload;

    public function __construct(
        public int   $product_variant_id,
        public float $price,
        public int   $category_id,
    )
    {
    }

    /**
     * Создаёт DTO из модели ProductVariant.
     *
     * @param ProductVariant $variant Экземпляр модели ProductVariant с необходимыми предзагруженными отношениями.
     * @return self
     * @throws RequiredRelationMissingException Если хотя бы одно из указанных отношений не загружено.
     * @throws RelationIsNullException Если загруженное отношение равно null.
     * @throws MissingProductCategoryException Если у связанного продукта отсутствует категория с типом product.
     * @see \App\Models\Product::getProductCategoryAttribute()
     */
    public static function fromModel(ProductVariant $variant): self
    {
        self::checkRequireAllRelationPaths($variant, 'product.productCategoryRelation');

        return new self(
            product_variant_id: $variant->id,
            price: $variant->price,
            category_id: $variant->product->productCategory->id,
        );
    }
}
