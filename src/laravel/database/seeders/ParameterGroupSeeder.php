<?php

namespace Database\Seeders;

use App\Enums\Parameter\ParameterGroupEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParameterGroupSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ParameterGroupEnum::cases() as $group) {
            DB::table('parameter_groups')
                ->updateOrInsert(
                    ['id' => $group->value],
                    ['slug' => $group->slug(), 'label' => $group->label()],
                );
        }
    }
}
