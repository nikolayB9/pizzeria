<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function streets(): HasMany
    {
        return $this->hasMany(Street::class);
    }
}
