<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Данные по умолчанию для создания адреса (без city_id и street_id).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $entrance = fake()->randomElement([null, rand(1, 15)]);
        $floor = $entrance ? rand(1, 25) : null;
        $flat = $entrance ? rand(1, 500) : null;
        $intercomCode = $flat ?? null;

        return [
            'user_id' => null,
            'house' => rand(1, 300),
            'entrance' => $entrance,
            'floor' => $floor,
            'flat' => $flat,
            'intercom_code' => $intercomCode,
        ];
    }
}
