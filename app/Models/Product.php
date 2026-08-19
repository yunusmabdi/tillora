<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use \Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'sku',
    'barcode',
    'name',
    'slug',
    'description',
    'image',
    'cost_price',
    'selling_price',
    'stock_quantity',
    'minimum_stock',
    'maximum_stock',
    'is_active',
    'unit',
])]
class Product extends Model
{
    use SoftDeletes;
    
    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock_quantity' => 'decimal:2',
            'minimum_stock' => 'decimal:2',
            'maximum_stock' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    protected static function booted(): void
    {
    static::creating(function (Product $product) {
        if (blank($product->sku)) {

            $lastId = DB::table('products')->max('id') + 1;

            $product->sku = 'PRD' . str_pad($lastId, 6, '0', STR_PAD_LEFT);
        }
    });
    }   
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->minimum_stock;
    }
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getDiscountedPriceAttribute(): float
    {
        if (! $this->discount_active) {
            return (float) $this->selling_price;
        }

        return match ($this->discount_type) {

            'percentage' => max(
                0,
                $this->selling_price -
                ($this->selling_price * ($this->discount_value / 100))
            ),

            'fixed' => max(
                0,
                $this->selling_price - $this->discount_value
            ),

            default => (float) $this->selling_price,
        };
    }
}