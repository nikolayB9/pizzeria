<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Parameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CategoryParameterSeeder extends Seeder
{
    public function run(): void
    {
        $this->syncCategoryParameters('pizza', [
            ['name' => 'Вес', 'unit' => 'г'],
            ['name' => 'ккал'],
            ['name' => 'белки'],
            ['name' => 'жиры'],
            ['name' => 'углеводы'],
            ['name' => 'Состав'],
            ['name' => 'Аллергены'],
        ]);

        $this->syncCategoryParameters('drinks', [
            ['name' => 'ккал'],
            ['name' => 'белки'],
            ['name' => 'жиры'],
            ['name' => 'углеводы'],
            ['name' => 'Состав'],
            ['name' => 'Аллергены'],
        ]);
    }

    /**
     * Привязывает параметры к категории.
     *
     * @param string $categorySlug
     * @param array<array{name: string, unit?: string|null}> $parameters
     */
    private function syncCategoryParameters(string $categorySlug, array $parameters): void
    {
        // Находим категорию по slug
        $category = Category::where('slug', $categorySlug)->first();

        if (!$category) {
            throw new \RuntimeException("Category with slug '$categorySlug' not found.");
        }

        // Получаем id параметров по name и unit (если указан)
        $parameterIds = $this->getParameterIds($parameters);

        if (count($parameters) !== $parameterIds->count()) {
            throw new \RuntimeException('One or more parameters not found for category: ' . $categorySlug);
        }

        // Привязываем параметры к категории (без удаления существующих)
        $category->parameters()->syncWithoutDetaching($parameterIds);
    }

    /**
     * Возвращает ID параметров по имени и (опционально) unit.
     *
     * @param array<array{name: string, unit?: string|null}> $parameters
     * @return Collection<int, int>
     */
    private function getParameterIds(array $parameters): Collection
    {
        // Инициализируем запрос к модели Parameter
        $query = Parameter::query();

        foreach ($parameters as $param) {
            // Добавляем OR-условие для каждого сочетания name + unit (или NULL)
            $query->orWhere(function ($q) use ($param) {
                $q->where('name', $param['name']);

                if (array_key_exists('unit', $param)) {
                    $q->where('unit', $param['unit']);
                } else {
                    $q->whereNull('unit');
                }
            });
        }

        // Получаем коллекцию id
        return $query->get()->pluck('id');
    }
}
