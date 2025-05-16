<?php

namespace App\Repositories\Category;

use App\Exceptions\Category\CategoryNotFoundException;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Возвращает коллекцию со всеми категориями.
     *
     * @return Collection Коллекция категорий.
     */
    public function getAll(): Collection
    {
        return Category::all();
    }

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

    /**
     * Возвращает slug категории по её ID.
     *
     * @param int $id ID категории.
     * @return string Slug категории.
     * @throws CategoryNotFoundException Если категория с заданным ID не найдена.
     */
    public function getSlugById(int $id): string
    {
        try {
            return Category::where('id', $id)->firstOrFail()->slug;
        } catch (ModelNotFoundException) {
            throw new CategoryNotFoundException("Категория с ID [$id] не найдена.");
        }
    }
}
