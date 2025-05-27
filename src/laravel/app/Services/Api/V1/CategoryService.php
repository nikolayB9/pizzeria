<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Category\CategoryListItemDto;
use App\Repositories\Api\V1\Category\CategoryRepositoryInterface;

class CategoryService
{
    public function __construct(private readonly CategoryRepositoryInterface $categoryRepository)
    {
    }

    /**
     * Возвращает все категории продуктов.
     *
     * @return CategoryListItemDto[] Массив DTO категорий.
     */
    public function getAllCategories(): array
    {
        $categories = $this->categoryRepository->getAll();

        return CategoryListItemDto::collection($categories);
    }
}
