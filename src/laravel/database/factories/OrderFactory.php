<?php

namespace Database\Factories;

use App\Enums\Order\OrderStatusEnum;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Данные по умолчанию для создания заказа (без user_id, address_id, delivery_cost, total).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => OrderStatusEnum::CREATED,
            'delivery_at' => new DateTimeImmutable(),
            'comment' => fake()->randomElement([null, fake()->sentence()]),
        ];
    }
}
