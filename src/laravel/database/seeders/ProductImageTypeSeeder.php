<?php

namespace Database\Seeders;

use App\Enums\ProductImage\ProductImageTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProductImageTypeEnum::cases() as $type) {
            DB::table('product_image_types')
                ->updateOrInsert(
                    ['id' => $type->value],
                    ['slug' => $type->slug(), 'label' => $type->label()],
                );
        }
    }
}
