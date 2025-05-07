<?php

namespace App\Repositories\Category;

use App\Exceptions\Category\CategoryNotFoundException;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Получает модель категории по slug с выборкой только поля id.
     *
     * @param string $slug Уникальный slug категории.
     * @return Category Экземпляр модели Category, в которой загружено только поле id.
     * @throws CategoryNotFoundException Если категория не найдена.
     */
    public function getModelWithOnlyIdBySlug(string $slug): Category
    {
        try {
            return Category::select('id')->where('slug', $slug)->firstOrFail();
        } catch (ModelNotFoundException) {
            throw new CategoryNotFoundException("Категория [$slug] не найдена.");
        }
    }
}
