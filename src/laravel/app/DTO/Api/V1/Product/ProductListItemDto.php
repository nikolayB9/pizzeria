<?php

namespace App\DTO\Api\V1\Product;

use App\DTO\Traits\RequiresPreload;
use App\Exceptions\System\Dto\AggregateAttributeMissingException;
use App\Exceptions\System\Dto\RelationIsNullException;
use App\Exceptions\System\Dto\RequiredRelationMissingException;
use App\Models\Product;
use Illuminate\Support\Collection;

class ProductListItemDto
{
    use RequiresPreload;

    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public string $slug,
        public string $preview_image_url,
        public bool $has_multiple_variants,
        public float $min_price,
    ) {
    }

    /**
     * Создаёт DTO из модели Product.
     *
     * @param Product $product Экземпляр модели продукта с предзагруженным отношением previewImage и агрегатами
     *     variants_count, variants_min_price.
     *
     * @return self
     * @throws RequiredRelationMissingException Если previewImage не был загружен.
     * @throws RelationIsNullException Если previewImage равен null.
     * @throws AggregateAttributeMissingException Если агрегаты variants_count или variants_min_price отсутствуют.
     */
    public static function fromModel(Product $product): self
    {
        self::checkRequireNotNullRelations($product, 'previewImage');
        self::checkRequireAggregateAttributes($product, ['variants_count', 'variants_min_price']);

        return new self(
            id: $product->id,
            name: $product->name,
            description: $product->description,
            slug: $product->slug,
            preview_image_url: url($product->previewImage->image_path ?: config('product.default_image_path')),
            has_multiple_variants: $product->variants_count > 1,
            min_price: (float)$product->variants_min_price,
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection $products Коллекция моделей Product.
     *
     * @return ProductListItemDto[] Массив DTO.
     * @throws RequiredRelationMissingException
     * @throws RelationIsNullException
     * @throws AggregateAttributeMissingException
     */
    public static function collection(Collection $products): array
    {
        return $products->map(fn($product) => self::fromModel($product))->toArray();
    }
}
