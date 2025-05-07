<?php

namespace App\Repositories\Product;

use App\Models\Category;
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
}
