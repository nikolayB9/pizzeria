<?php

namespace Database\Seeders;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@mail.ru')->firstOrFail();
        $address = Address::firstOrFail();

        $orderData = [
            'user_id' => $user->id,
            'address_id' => $address->id,
            'delivery_price' => 155,
            'total_price' => 1353,
            'delivery_time' => now(),
            'status' => OrderStatusEnum::CREATED->value,
            'comment' => null,
        ];

        if (!Order::where($orderData)->exists()) {
            Order::create($orderData);
        }
    }
}
