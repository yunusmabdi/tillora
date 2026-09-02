<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class InventoryService
{
    /**
     * Ensure all products in a sale have sufficient stock.
     */
    public function validateSaleStock(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            $product = $item->product;

            if (! $product) {
                throw new RuntimeException('Product not found.');
            }

            if ($product->stock_quantity < $item->quantity) {
                throw new RuntimeException(
                    "{$product->name} has only {$product->stock_quantity} item(s) in stock."
                );
            }
        }
    }

    /**
     * Deduct stock for a completed sale.
     */
    public function issueSaleStock(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            $product = Product::lockForUpdate()
                ->find($item->product_id);

            if (! $product) {
                throw new RuntimeException('Product not found.');
            }

            if ($product->stock_quantity < $item->quantity) {
                throw new RuntimeException(
                    "{$product->name} has only {$product->stock_quantity} item(s) in stock."
                );
            }

            $product->decrement(
                'stock_quantity',
                $item->quantity
            );

            StockMovement::create([
                'product_id'     => $product->id,
                'type'           => 'OUT',
                'quantity'       => $item->quantity,
                'reference_type' => Sale::class,
                'reference_id'   => $sale->id,
                'description'    => "Sale {$sale->invoice_number}",
            ]);
        }
    }

    /**
     * Increase stock from a received purchase.
     */
    public function receivePurchaseStock(Purchase $purchase): void
    {
        foreach ($purchase->items as $item) {
            $product = Product::lockForUpdate()
                ->find($item->product_id);

            if (! $product) {
                throw new RuntimeException('Product not found.');
            }

            $product->increment(
                'stock_quantity',
                $item->quantity
            );

            StockMovement::create([
                'product_id'     => $product->id,
                'type'           => 'IN',
                'quantity'       => $item->quantity,
                'reference_type' => Purchase::class,
                'reference_id'   => $purchase->id,
                'description'    => "Purchase {$purchase->purchase_number}",
            ]);
        }
    }
}