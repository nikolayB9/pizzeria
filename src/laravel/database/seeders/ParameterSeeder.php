<?php

namespace Database\Seeders;

use App\Enums\Parameter\ParameterGroupEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParameterSeeder extends Seeder
{
    public function run(): void
    {
        $this->updateOrInsertParameters($this->getParametersOfFeaturesGroup(), ParameterGroupEnum::Features->value);
        $this->updateOrInsertParameters($this->getParametersOfNutritionGroup(), ParameterGroupEnum::Nutrition->value);
        $this->updateOrInsertParameters($this->getParametersOfIngredientsGroup(), ParameterGroupEnum::Ingredients->value);
        $this->updateOrInsertParameters($this->getParametersOfAllergensGroup(), ParameterGroupEnum::Allergens->value);
    }

    private function updateOrInsertParameters(array $parameters, int $parameterGroupId): void
    {
        foreach ($parameters as $parameter) {
            DB::table('parameters')
                ->updateOrInsert(
                    [
                        'name' => $parameter['name'],
                        'unit' => $parameter['unit'] ?? null,
                        'group' => $parameterGroupId,
                    ]);
        }
    }

    /**
     * Параметры группы "Характеристики" (например, "Вес").
     */
    private function getParametersOfFeaturesGroup(): array
    {
        return [
            [
                'name' => 'Вес',
                'unit' => 'г',
            ],
        ];
    }

    /**
     * Параметры группы "Пищевая ценность" (например, "белки").
     */
    private function getParametersOfNutritionGroup(): array
    {
        return [
            [
                'name' => 'ккал',
            ],
            [
                'name' => 'белки',
            ],
            [
                'name' => 'жиры',
            ],
            [
                'name' => 'углеводы',
            ],
        ];
    }

    /**
     * Параметры группы "Состав" (группа состоит из одного параметра - "Состав").
     * Описание состава продукта: мука, сыр и т.п.
     */
    private function getParametersOfIngredientsGroup(): array
    {
        return [
            [
                'name' => 'Состав',
            ],
        ];
    }

    /**
     * Параметры группы "Аллергены" (группа состоит из одного параметра - "Аллергены").
     * Описание потенциальных аллергенов продукта: глютен, молоко и т.п.
     */
    private function getParametersOfAllergensGroup(): array
    {
        return [
            [
                'name' => 'Аллергены',
            ],
        ];
    }

}
