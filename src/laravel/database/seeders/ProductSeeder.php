<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->getProducts() as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                [
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'is_published' => $product['is_published'],
                ]
            );
        }
    }

    private function getProducts(): array
    {
        return [
            [
                'name' => 'Ветчина и сыр',
                'description' => 'Ветчина, моцарелла, фирменный соус альфредо',
                'is_published' => true,
                'slug' => 'vetcina-i-syr',
            ],
            [
                'name' => 'Карбонара',
                'description' => 'Бекон, сыры чеддер и пармезан, моцарелла, томаты, красный лук, чеснок',
                'is_published' => true,
                'slug' => 'karbonara',
            ],
            [
                'name' => 'Какао',
                'description' => 'Насыщенное,плотное и такое знакомое какао с молоком',
                'is_published' => true,
                'slug' => 'kakao',
            ],
            [
                'name' => 'Шоколадный молочный коктейль',
                'description' => 'Шоколадный милшкейк со сливочным мороженым и фирменным какао',
                'is_published' => true,
                'slug' => 'sokoladnyi-molocnyi-kokteil',
            ],
        ];
    }
}
