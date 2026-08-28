<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DeliveryZone;
use App\Models\SaleStatusHistory;
use App\Models\User;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'sale_date',
        'status',

        'payment_status',
        'fulfillment_status',

        'payment_method',

        'subtotal',
        'discount',
        'tax',
        'total_amount',

        'amount_paid',
        'advance_amount',
        'balance_amount',
        'change_amount',

        'delivery_zone_id',
        'delivery_address',
        'delivery_fee',

        'approved_by',
        'approved_at',
        'rejection_reason',
        
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function ($sale) {

            if (! $sale->invoice_number) {

                $lastSale = static::latest('id')->first();

                $nextNumber = $lastSale
                    ? $lastSale->id + 1
                    : 1;

                $sale->invoice_number = 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }
        });
    }


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }


    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
    protected function casts(): array
    {
        return [
            'sale_date' => 'datetime',
            'approved_at' => 'datetime',

            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total_amount' => 'decimal:2',

            'amount_paid' => 'decimal:2',
            'advance_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(SaleStatusHistory::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}