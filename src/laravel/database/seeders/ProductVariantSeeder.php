<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $this->updateOrCreateProductVariants('vetcina-i-syr', [
            [
                'name' => '25см',
                'price' => 599,
            ],
            [
                'name' => '30см',
                'price' => 919,
            ],
            [
                'name' => '35см',
                'price' => 1019,
            ],
        ]);

        $this->updateOrCreateProductVariants('karbonara', [
            [
                'name' => '25см',
                'price' => 719,
            ],
            [
                'name' => '30см',
                'price' => 1039,
            ],
            [
                'name' => '35см',
                'price' => 1229,
            ],
        ]);

        $this->updateOrCreateProductVariants('kakao', [
            [
                'name' => '0,3л',
                'price' => 139,
            ],
        ]);

        $this->updateOrCreateProductVariants('sokoladnyi-molocnyi-kokteil', [
            [
                'name' => '0,3л',
                'price' => 235,
            ],
        ]);
    }

    /**
     * @param string $productSlug
     * @param array<array{name: string, price: float|int, old_price?: float|int|null}> $productVariants
     */
    private function updateOrCreateProductVariants(string $productSlug, array $productVariants): void
    {
        $product = Product::select('id')->where('slug', $productSlug)->firstOrFail();

        foreach ($productVariants as $variant) {
            ProductVariant::updateOrCreate(
                ['name' => $variant['name'], 'product_id' => $product->id],
                ['price' => $variant['price'], 'old_price' => $variant['old_price'] ?? null]
            );
        }
    }
}
