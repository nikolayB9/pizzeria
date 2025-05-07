<?php

namespace App\Repositories\Product;

use App\Exceptions\Product\ProductNotFoundException;
use App\Models\Category;
use App\Models\Product;
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
}
