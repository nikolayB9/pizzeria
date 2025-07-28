<?php

namespace Database\Seeders;

use App\Enums\Payment\PaymentGatewayEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        foreach (PaymentGatewayEnum::cases() as $gateway) {
            DB::table('payment_gateways')
                ->updateOrInsert(
                    ['id' => $gateway->value],
                    ['slug' => $gateway->slug(), 'label' => $gateway->label()],
                );
        }
    }
}
