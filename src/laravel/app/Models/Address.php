<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $with = ['city', 'street'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function street(): BelongsTo
    {
        return $this->belongsTo(Street::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
