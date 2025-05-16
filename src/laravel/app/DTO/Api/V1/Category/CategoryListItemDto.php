<?php

namespace App\DTO\Api\V1\Category;

use App\DTO\Traits\RequiresPreload;
use App\Enums\Category\CategoryTypeEnum;
use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryListItemDto
{
    use RequiresPreload;

    public function __construct(
        public int     $id,
        public string  $name,
        public string  $slug,
        public string  $type_slug,
    )
    {
    }

    /**
     * Создаёт DTO из модели Category.
     *
     * @param Category $category Экземпляр модели категории.
     * @return self
     */
    public static function fromModel(Category $category): self
    {
        $typeSlug = $category->type instanceof CategoryTypeEnum
            ? $category->type->slug()
            : CategoryTypeEnum::tryFrom($category->type)->slug();

        return new self(
            id: $category->id,
            name: $category->name,
            slug: $category->slug,
            type_slug: $typeSlug,
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection $categories Коллекция моделей Category.
     * @return CategoryListItemDto[] Массив DTO.
     */
    public static function collection(Collection $categories): array
    {
        return $categories->map(fn($category) => self::fromModel($category))->toArray();
    }
}
