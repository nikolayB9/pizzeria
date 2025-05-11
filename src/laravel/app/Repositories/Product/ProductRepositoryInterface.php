<?php

namespace App\Repositories\Product;

use App\Exceptions\Product\ProductNotFoundException;
use App\Exceptions\Product\ProductNotPublishedException;
use App\Exceptions\Product\ProductVariantNotFoundException;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /**
     * Возвращает опубликованные продукты для заданной категории.
     *
     * @param Category $category
     * @return Collection
     */
    public function getPublishedForCategory(Category $category): Collection;

    /**
     * Возвращает продукт по его slug.
     *
     * @param string $slug
     * @return Product
     * @throws ProductNotFoundException
     */
    public function getBySlug(string $slug): Product;

    /**
     * Возвращает вариант опубликованного продукта по его ID с категорией типа "product".
     *
     * @param int $id
     * @return ProductVariant
     * @throws ProductVariantNotFoundException
     * @throws ProductNotPublishedException
     */
    public function getProductVariantWithCategoryById(int $id): ProductVariant;
}
