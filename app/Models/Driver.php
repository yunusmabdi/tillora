<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}