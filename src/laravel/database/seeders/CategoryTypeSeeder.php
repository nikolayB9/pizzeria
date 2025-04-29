<?php

namespace Database\Seeders;

use App\Enums\Category\CategoryTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (CategoryTypeEnum::cases() as $type) {
            DB::table('category_types')
                ->updateOrInsert(
                  ['id' => $type->value],
                  ['slug' => $type->slug(), 'label' => $type->label()],
                );
        }
    }
}
