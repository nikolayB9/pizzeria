<?php

namespace Database\Seeders;

use App\Enums\Order\OrderStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (OrderStatusEnum::cases() as $status) {
            DB::table('order_statuses')
                ->updateOrInsert(
                    ['id' => $status->value],
                    ['slug' => $status->slug(), 'label' => $status->label()],
                );
        }
    }
}
