<?php

namespace App\DTO\Api\V1\Product;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class VariantDto
{
    public function __construct(
        public int               $id,
        public string            $name,
        public float|string      $price,
        public null|float|string $old_price,
    )
    {
    }

    /**
     * Создаёт DTO из модели ProductVariant.
     *
     * @param ProductVariant $variant Экземпляр модели варианта продукта.
     * @return self
     */
    public static function fromModel(ProductVariant $variant): self
    {
        return new self(
            id: $variant->id,
            name: $variant->name,
            price: (float)$variant->price,
            old_price: is_null($variant->old_price) ? null : (float)$variant->old_price,
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection $variants Коллекция моделей ProductVariant.
     * @return array Массив DTO.
     */
    public static function collection(Collection $variants): array
    {
        return $variants->map(fn($variant) => self::fromModel($variant))->toArray();
    }
}
