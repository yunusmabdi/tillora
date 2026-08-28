<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'sale_date',
        'status',

        // Payment
        'payment_status',
        'payment_method',

        // Order / fulfillment
        'fulfillment_status',

        // Approval
        'approved_by',
        'approved_at',
        'rejection_reason',

        // Amounts
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'amount_paid',
        'advance_amount',
        'balance_amount',
        'change_amount',

        // Delivery
        'delivery_zone_id',
        'delivery_address',
        'delivery_fee',

        'notes',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Boot
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($sale) {

            if (! $sale->invoice_number) {

                $lastSale = static::latest('id')->first();

                $nextNumber = $lastSale
                    ? $lastSale->id + 1
                    : 1;

                $sale->invoice_number =
                    'INV-' . str_pad(
                        $nextNumber,
                        5,
                        '0',
                        STR_PAD_LEFT
                    );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Customer
    |--------------------------------------------------------------------------
    */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Sale Items
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Approval
    |--------------------------------------------------------------------------
    */

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Order / Fulfillment Status History
    |--------------------------------------------------------------------------
    */

    public function statusHistories(): HasMany
    {
        return $this->hasMany(SaleStatusHistory::class)
            ->orderBy('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Delivery Zone
    |--------------------------------------------------------------------------
    */

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(
            DeliveryZone::class,
            'delivery_zone_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | POS User
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

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
}