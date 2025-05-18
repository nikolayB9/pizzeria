<?php

namespace App\Services\Api\V1;

use App\DTO\Api\V1\Product\ProductDto;
use App\DTO\Api\V1\Product\ProductListItemDto;
use App\Exceptions\Category\CategoryNotFoundException;
use App\Exceptions\Product\ProductNotFoundException;
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
     * @param string $slug Slug категории.
     * @return array Массив DTO продуктов, либо пустой массив, если таких нет.
     * @throws CategoryNotFoundException Если категория не найдена.
     */
    public function getProductsByCategorySlug(string $slug): array
    {
        $category = app(CategoryRepositoryInterface::class)->getModelWithOnlyIdBySlug($slug);
        $products = $this->productRepository->getPublishedForCategory($category);

        if ($products->isEmpty()) {
            return [];
        }

        return ProductListItemDto::collection($products);
    }

    /**
     * Возвращает DTO опубликованного продукта по его slug.
     *
     * @param string $slug Slug продукта.
     * @return ProductDto DTO продукта.
     * @throws ProductNotFoundException Если продукт не найден.
     */
    public function getProductBySlug(string $slug): ProductDto
    {
        $product = $this->productRepository->getBySlug($slug);

        return ProductDto::fromModel($product);
    }
}
