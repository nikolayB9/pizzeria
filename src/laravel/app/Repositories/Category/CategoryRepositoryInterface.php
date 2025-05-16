<?php

namespace App\Repositories\Category;

use App\Exceptions\Category\CategoryNotFoundException;
use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Возвращает коллекцию со всеми категориями.
     *
     * @return Collection Коллекция категорий.
     */
    public function getAll(): Collection;

    /**
     * Возвращает категорию с полем id по её slug.
     *
     * @param string $slug
     *
     * @return Category
     * @throws CategoryNotFoundException
     */
    public function getModelWithOnlyIdBySlug(string $slug): Category;

    /**
     * Возвращает slug категории по её ID.
     *
     * @param int $id
     *
     * @return string
     * @throws CategoryNotFoundException
     */
    public function getSlugById(int $id): string;
}
