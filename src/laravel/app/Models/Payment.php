<?php

namespace App\Models;

use App\Enums\Payment\PaymentGatewayEnum;
use App\Enums\Payment\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'status' => PaymentStatusEnum::class,
        'gateway' => PaymentGatewayEnum::class,
    ];
}
