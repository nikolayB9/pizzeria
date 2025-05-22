<?php

namespace App\DTO\Api\V1\Order;

class CreateOrderInputDto
{
    public function __construct(
        public string  $delivery_time,
        public ?string $comment,
    )
    {
    }
}
