<?php

namespace App\Repositories\Category;

use App\Exceptions\Category\CategoryNotFoundException;
use App\Models\Category;

interface CategoryRepositoryInterface
{
    /**
     * Возвращает категорию с полем id по её slug.
     *
     * @param string $slug
     * @return Category
     * @throws CategoryNotFoundException
     */
    public function getModelWithOnlyIdBySlug(string $slug): Category;
}
