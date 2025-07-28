<?php

namespace Database\Seeders;

use App\Enums\Payment\PaymentStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PaymentStatusEnum::cases() as $status) {
            DB::table('payment_statuses')
                ->updateOrInsert(
                    ['id' => $status->value],
                    ['slug' => $status->slug(), 'label' => $status->label()],
                );
        }
    }
}
