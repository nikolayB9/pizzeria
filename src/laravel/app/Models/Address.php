<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function city()
    {
        return $this->belongsToMany(City::class);
    }
}
