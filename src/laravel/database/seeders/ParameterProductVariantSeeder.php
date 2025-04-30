<?php

namespace Database\Seeders;

use App\Models\Parameter;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ParameterProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $productVariantParameters = [
            'vetcina-i-syr' => [
                '25см' => [
                    'Вес' => ['value' => '430', 'is_shared' => false, 'unit' => 'г'],
                    'ккал' => ['value' => '216.4', 'is_shared' => true],
                    'белки' => ['value' => '9.0', 'is_shared' => true],
                    'жиры' => ['value' => '7.6', 'is_shared' => true],
                    'углеводы' => ['value' => '28.1', 'is_shared' => true],
                    'Состав' => ['value' => 'Тесто (мука, вода, масло подсолнечное, сахар, соль, томаты, соус.', 'is_shared' => true],
                    'Аллергены' => ['value' => 'Может содержать: глютен, молоко и продукты его переработки (в том числе лактозу).', 'is_shared' => true],
                ],
                '30см' => [
                    'Вес' => ['value' => '610', 'is_shared' => false, 'unit' => 'г'],
                ],
                '35см' => [
                    'Вес' => ['value' => '860', 'is_shared' => false, 'unit' => 'г'],
                ],
            ],
            'karbonara' => [
                '25см' => [
                    'Вес' => ['value' => '340', 'is_shared' => false, 'unit' => 'г'],
                    'ккал' => ['value' => '310.4', 'is_shared' => true],
                    'белки' => ['value' => '12.4', 'is_shared' => true],
                    'жиры' => ['value' => '12.5', 'is_shared' => true],
                    'углеводы' => ['value' => '35.0', 'is_shared' => true],
                    'Состав' => ['value' => 'Тесто (мука, вода, масло подсолнечное, сахар, соль, дрожжи, томатный соус, пикантная пепперони, моцарелла.', 'is_shared' => true],
                    'Аллергены' => ['value' => 'Может содержать: глютен', 'is_shared' => true],
                ],
                '30см' => [
                    'Вес' => ['value' => '550', 'is_shared' => false, 'unit' => 'г'],
                ],
                '35см' => [
                    'Вес' => ['value' => '760', 'is_shared' => false, 'unit' => 'г'],
                ],
            ],
            'kakao' => [
                '0,3л' => [
                    'ккал' => ['value' => '174', 'is_shared' => true],
                    'белки' => ['value' => '5.3', 'is_shared' => true],
                    'жиры' => ['value' => '5', 'is_shared' => true],
                    'углеводы' => ['value' => '25.7', 'is_shared' => true],
                    'Состав' => ['value' => 'Смесь для приготовления како-напитка (како-порошок, молоко сухое цельное).', 'is_shared' => true],
                    'Аллергены' => ['value' => 'Может содержать: глютен, молоко и продукты его переработки.', 'is_shared' => true],
                ],
            ],
            'sokoladnyi-molocnyi-kokteil' => [
                '0,3л' => [
                    'ккал' => ['value' => '467.3', 'is_shared' => true],
                    'белки' => ['value' => '10.6', 'is_shared' => true],
                    'жиры' => ['value' => '21.3', 'is_shared' => true],
                    'углеводы' => ['value' => '54.9', 'is_shared' => true],
                    'Состав' => ['value' => 'Мороженое, молоко, какао.', 'is_shared' => true],
                    'Аллергены' => ['value' => 'Может содержать: глютен, молоко и продукты его переработки.', 'is_shared' => true],
                ],
            ],
        ];

        $this->syncProductVariantParameters($productVariantParameters);
    }

    /**
     * @param array<array<array<array{value: string, is_shared: boolean, unit?: ?string}>>> $data
     */
    private function syncProductVariantParameters(array $data): void
    {
        // Находим продукт по его slug, параметры продукта
        foreach ($data as $productSlug => $productVariantsWithParams) {
            $product = Product::select('id', 'name')
                ->where('slug', $productSlug)
                ->firstOrFail();
            $productParameterIds = $product->parameters()->pluck('id');

            // Находим варианты продукта по product_id и name
            foreach ($productVariantsWithParams as $productVariantName => $params) {
                $productVariant = ProductVariant::select('id')
                    ->where('product_id', $product->id)
                    ->where('name', $productVariantName)
                    ->firstOrFail();

                // Находим параметры по name и unit
                foreach ($params as $parameterName => $paramData) {
                    $parameter = Parameter::select('id', 'name')
                        ->where('name', $parameterName)
                        ->where('unit', $paramData['unit'] ?? null)
                        ->firstOrFail();

                    if (!$productParameterIds->contains($parameter->id)) {
                        throw new \RuntimeException("Parameter '$parameter->name' is not allowed for product '$product->name'.");
                    }

                    // Обновляем/добавляем параметры Варианта с его значениями
                    $productVariant->parameters()->syncWithoutDetaching([
                        $parameter->id => [
                            'value' => $paramData['value'],
                            'is_shared' => $paramData['is_shared'],
                        ],
                    ]);
                }
            }
        }
    }
}
