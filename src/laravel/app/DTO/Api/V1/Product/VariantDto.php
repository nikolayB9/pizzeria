<?php

namespace App\DTO\Api\V1\Product;

use App\DTO\Traits\RequiresPreload;
use App\Exceptions\System\Dto\RequiredRelationMissingException;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class VariantDto
{
    use RequiresPreload;

    public function __construct(
        public int $id,
        public string $name,
        public float $price,
        public null|float $old_price,
        public array $parameters,
    ) {
    }

    /**
     * Создаёт DTO из модели ProductVariant.
     *
     * @param ProductVariant $variant Экземпляр модели с предзагруженным отношением parameters.
     *
     * @return self
     * @throws RequiredRelationMissingException Если отношение parameters не было предварительно загружено.
     */
    public static function fromModel(ProductVariant $variant): self
    {
        self::checkRequireRelations($variant, 'parameters');

        return new self(
            id: $variant->id,
            name: $variant->name,
            price: (float)$variant->price,
            old_price: is_null($variant->old_price) ? null : (float)$variant->old_price,
            parameters: ParameterDto::collection($variant->parameters),
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection $variants Коллекция моделей ProductVariant.
     *
     * @return VariantDto[] Массив DTO.
     */
    public static function collection(Collection $variants): array
    {
        return $variants->map(fn($variant) => self::fromModel($variant))->toArray();
    }
}
