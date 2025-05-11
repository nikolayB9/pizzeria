<?php

namespace App\DTO\Api\V1\Cart;

use App\DTO\Traits\RequiresPreload;
use App\Exceptions\Dto\RelationIsNullException;
use App\Exceptions\Dto\RequiredRelationMissingException;
use App\Exceptions\Product\MissingProductCategoryException;
use App\Models\Cart;
use Illuminate\Support\Collection;

class CartProductListItemDto
{
    use RequiresPreload;

    public function __construct(
        public string $name,
        public int    $variant_id,
        public string $variant_name,
        public float  $price,
        public int    $qty,
        public string $preview_image_url,
        public int    $category_id,
    )
    {
    }

    /**
     * Создаёт DTO из модели Cart.
     *
     *  Требует, чтобы были предварительно загружены следующие отношения:
     *  productVariant.product.previewImage, productVariant.product.productCategoryRelation.
     *
     * @param Cart $cart Экземпляр модели Cart с необходимыми предзагруженными отношениями.
     * @return self
     * @throws RequiredRelationMissingException Если одно из указанных отношений не загружено.
     * @throws RelationIsNullException Если загруженное отношение равно null.
     * @throws MissingProductCategoryException Если у продукта отсутствует категория с типом product.
     * @see \App\Models\Product::getProductCategoryAttribute()
     */
    public static function fromModel(Cart $cart): self
    {
        self::checkRequireAllRelationPaths($cart, [
            'productVariant.product.previewImage',
            'productVariant.product.productCategoryRelation',
        ]);

        $product = $cart->productVariant->product;

        return new self(
            name: $product->name,
            variant_id: $cart->product_variant_id,
            variant_name: $cart->productVariant->name,
            price: (float)$cart->price,
            qty: $cart->qty,
            preview_image_url: url($product->previewImage->image_path ?: config('product.default_image_path')),
            category_id: $product->productCategory->id,
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection $cart Коллекция моделей Cart.
     * @return CartProductListItemDto[] Массив DTO.
     * @throws RequiredRelationMissingException
     * @throws RelationIsNullException
     * @throws MissingProductCategoryException
     */
    public static function collection(Collection $cart): array
    {
        return $cart->map(fn($cartItem) => self::fromModel($cartItem))->toArray();
    }
}
