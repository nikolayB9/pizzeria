<?php

namespace Database\Seeders;

use App\DTO\Api\V1\Cart\CartRawItemDto;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\User\UserRoleEnum;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Api\V1\CartService;
use App\Services\Api\V1\CheckoutService;
use App\Services\Api\V1\OrderService;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function __construct(private readonly CartService     $cartService,
                                private readonly CheckoutService $checkoutService,
                                private readonly OrderService    $orderService)
    {
    }

    public function run(): void
    {
        $countPerUser = config('seeder.orders_per_user');

        $users = User::where('role', UserRoleEnum::User->value)->get();

        $variants = ProductVariant::all();

        foreach ($users as $user) {
            $defaultAddress = $user->defaultAddress;

            if (!$defaultAddress) {
                $this->command->info(
                    "У пользователя {$user->email} не задан дефолтный адрес, пропуск создания заказов."
                );
                continue;
            }

            $addressId = $defaultAddress->id;

            for ($i = 1; $i <= $countPerUser; $i++) {
                $randVariants = $variants->random(rand(1, 3));
                $products = $randVariants->map(fn($variant) => new CartRawItemDto(
                    product_variant_id: $variant->id,
                    price: $variant->price,
                    qty: rand(1, 3),
                )
                )->toArray();

                $total = $this->cartService->getTotalPrice($products);
                $deliveryCost = $this->checkoutService->calculateDeliveryCostByCartTotal($total);

                $randStatus = OrderStatusEnum::cases()[array_rand(OrderStatusEnum::cases())];

                $slots = $this->checkoutService->getDeliverySlots();
                $randKey = array_rand($slots);
                $time = $slots[$randKey]['from'];
                $deliveryAt = $this->orderService->parseAndValidateDeliveryTime($time);

                $order = Order::factory()->create([
                    'user_id' => $user->id,
                    'address_id' => $addressId,
                    'delivery_cost' => $deliveryCost,
                    'total' => $total,
                    'status' => $randStatus,
                    'delivery_at' => $deliveryAt,
                ]);

                $arrayToInsert = CartRawItemDto::toOrderProductInsertData($products);
                $order->products()->attach($arrayToInsert);
            }

            $this->command->info("Создано $countPerUser заказов для пользователя {$user->email}");
        }
    }
}
