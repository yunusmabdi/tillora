<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    protected $fillable = [
        'name',
        'description',
        'min_distance',
        'max_distance',
        'fee',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_distance' => 'decimal:2',
            'max_distance' => 'decimal:2',
            'fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}