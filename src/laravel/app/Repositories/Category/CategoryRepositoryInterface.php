<?php

namespace App\Repositories\Category;

use App\Exceptions\Category\CategoryNotFoundException;
use App\Models\Category;

interface CategoryRepositoryInterface
{
    /**
     * Возвращает категорию по её slug.
     *
     * @param string $slug
     * @return Category
     * @throws CategoryNotFoundException
     */
    public function getBySlug(string $slug): Category;
}
