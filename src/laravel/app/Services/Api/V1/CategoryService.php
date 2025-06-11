<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Category\CategoryListItemDto;
use App\Repositories\Api\V1\Category\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Cache;

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
        return Cache::remember('categories_list', 3600, function () {
            $categories = $this->categoryRepository->getAll();
            return CategoryListItemDto::collection($categories);
        });
    }
}
