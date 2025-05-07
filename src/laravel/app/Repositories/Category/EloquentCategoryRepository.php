<?php

namespace App\Repositories\Category;

use App\Exceptions\Category\CategoryNotFoundException;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Получает категорию по slug или выбрасывает исключение, если не найдена.
     *
     * @param string $slug Уникальный slug категории.
     * @return Category Модель категории со всеми полями.
     * @throws CategoryNotFoundException Если категория не найдена.
     */
    public function getBySlug(string $slug): Category
    {
        try {
            return Category::where('slug', $slug)->firstOrFail();
        } catch (ModelNotFoundException) {
            throw new CategoryNotFoundException("Категория [$slug] не найдена.");
        }
    }
}
