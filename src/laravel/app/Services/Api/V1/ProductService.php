<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Product\ProductListItemDto;
use App\Exceptions\Category\CategoryNotFoundException;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;

class ProductService
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * Возвращает опубликованные продукты категории по её slug.
     *
     * @param string $categorySlug Slug категории.
     * @return array Массив DTO продуктов, либо пустой массив, если таких нет.
     *
     * @throws CategoryNotFoundException Если категория не найдена.
     */
    public function getProductsByCategorySlug(string $categorySlug): array
    {
        $category = app(CategoryRepositoryInterface::class)->getBySlug($categorySlug);
        $products = $this->productRepository->getPublishedForCategory($category);

        if ($products->isEmpty()) {
            return [];
        }

        return ProductListItemDto::collection($products);
    }
}
