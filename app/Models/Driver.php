<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'vehicle_type',
        'vehicle_number',
        'status',
        'address',
        'notes',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}