<?php

namespace Tests\Helpers;

use App\Models\Order;
use App\Models\User;
use App\Services\Api\V1\CheckoutService;
use Database\Seeders\OrderStatusSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OrderHelper
{
    /**
     * Создает один или несколько заказов, предварительно создавая все необходимые для заказа сущности.
     *
     * @param int $count Количество заказов.
     * @param int $countUsers Количество пользователей (для каждого заказа выбирается случайный пользователь).
     * @param int $countProducts Количество продуктов для каждой категории.
     * @param int $countProductVariants Количество вариантов (для каждого заказа выбираются случайные варианты).
     * @param int $countCategories Количество категорий.
     *
     * @return Order|Collection Один заказ или коллекция заказов.
     */
    public static function createOrders(int $count = 1,
                                        int $countUsers = 1,
                                        int $countProducts = 1,
                                        int $countProductVariants = 1,
                                        int $countCategories = 1): Order|Collection
    {
        $users = UserHelper::createUser($countUsers);
        $collectionUsers = collect()->wrap($users);

        $variants = ProductHelper::createProductVariantsAndCategories(
            $countProducts,
            $countProductVariants,
            $countCategories,
        );

        $orders = collect();

        while ($count > 1) {
            $user = $collectionUsers->random();
            $randVariants = $variants->random(rand(1, $countProductVariants));

            $orders->push(
                self::createOrdersForUsers($user, $randVariants)
            );

            $count--;
        }

        return $orders->count() === 1 ? $orders->first() : $orders;
    }


    /**
     * Создает заказы для указанных пользователей.
     *
     * @param User|Collection $users Пользователь или коллекция пользователей.
     * @param Collection|null $productVariants Варианты продуктов для создания заказов.
     * @param int $countOrders Количество создаваемых заказов.
     * @param bool $randomCountVariants True, если из переданных вариантов продуктов надо выбрать случайное количество.
     * @param bool $createDefaultAddress True, если необходимо создать для пользователей дефолтный адрес.
     *
     * @return Order|Collection Заказ или коллекция созданных заказов.
     */
    public static function createOrdersForUsers(User|Collection $users,
                                                ?Collection     $productVariants = null,
                                                int             $countOrders = 1,
                                                bool            $randomCountVariants = false,
                                                bool            $createDefaultAddress = true): Order|Collection

    {
        (new OrderStatusSeeder())->run();

        $collection = collect()->wrap($users);
        $orders = collect();

        if (is_null($productVariants)) {
            $productVariants = ProductHelper::createProductVariantsAndCategories(
                3,
                3,
                3,
            );
        }

        foreach ($collection as $user) {

            $address = $createDefaultAddress
                ? AddressHelper::createAddresses($user->id)
                : $user->defaultAddress;

            for ($i = 0; $i < $countOrders; $i++) {
                if ($randomCountVariants) {
                    $count = $productVariants->count();
                    $variants = $productVariants->random(rand(1, $count));
                } else {
                    $variants = $productVariants;
                }

                $total = round($variants->sum('price'), 2);
                $deliveryCost = app(CheckoutService::class)->calculateDeliveryCostByCartTotal($total);

                $createdAt = Carbon::create(2025, 1, 1, 15, 00, 00)->subMinutes($i); // ⬅️ не subMinutes, а addMinutes

                $deliveryBase = $createdAt->copy()->addMinutes(45);

                $minute = (int)$deliveryBase->format('i');
                $roundedMinutes = ceil($minute / 15) * 15;
                $roundedHour = (int)$deliveryBase->format('H') + intdiv($roundedMinutes, 60);

                $deliveryAt = $deliveryBase->copy()->setTime(
                    $roundedHour,
                    $roundedMinutes % 60
                );

                $order = Order::factory()->createQuietly([
                    'user_id' => $user->id,
                    'address_id' => $address->id,
                    'delivery_cost' => $deliveryCost,
                    'total' => $total,
                    'delivery_at' => $deliveryAt->format('Y-m-d H:i:s'),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                foreach ($variants as $variant) {
                    $order->products()->attach($variant, [
                        'price' => $variant->price,
                        'qty' => 1,
                    ]);
                }

                $orders->push($order);
            }
        }

        return $orders->count() === 1 ? $orders->first() : $orders;
    }
}
