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
        'user_id',
        'store_id',
        'sale_date',
        'status',

        // Payment
        'payment_status',
        'payment_method',
        'transaction_reference',

        // Fulfillment
        'fulfillment_status',
        'cancellation_reason',

        // Amounts
        'subtotal',
        'discount',
        'discount_amount',
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

                $sale->invoice_number = 'INV-' . str_pad(
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
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
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

            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'advance_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}