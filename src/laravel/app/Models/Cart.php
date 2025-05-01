<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    const UPDATED_AT = null;

    protected $table = 'cart';
    protected $guarded = ['id', 'created_at'];
}
