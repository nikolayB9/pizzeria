<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderProductSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@mail.ru')->firstOrFail();
        $order = Order::where('user_id', $user->id)
            ->where('total_price', 1353)
            ->firstOrFail();
        $product = Product::where('slug', 'vetcina-i-syr')->firstOrFail();
        $productVariant = ProductVariant::where('product_id', $product->id)
            ->where('name', '25см')
            ->where('price', 599)
            ->firstOrFail();

        $order->products()->syncWithoutDetaching([
            $productVariant->id => [
                'qty' => 2,
                'price' => 599,
            ],
        ]);
    }
}
