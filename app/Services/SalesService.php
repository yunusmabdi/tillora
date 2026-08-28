<?php

namespace App\Services;

use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {
    }

    /**
     * Complete a sale and deduct inventory.
     *
     * Used by the POS.
     */
    public function completeSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale->loadMissing('items.product');

            $this->inventoryService->validateSaleStock($sale);
            $this->inventoryService->issueSaleStock($sale);

            $sale->update([
                'status' => 'Completed',
            ]);
        });
    }

    /**
     * Create a customer order.
     *
     * Customer orders are stored in the sales table.
     *
     * Stock is reserved/deducted immediately when
     * the customer places the order.
     */
    public function createCustomerOrder(
        int $customerId,
        array $items,
        string $deliveryAddress,
        int $deliveryZoneId,
        float $advanceAmount,
        ?string $notes = null,
    ): Sale {
        return DB::transaction(function () use (
            $customerId,
            $items,
            $deliveryAddress,
            $deliveryZoneId,
            $advanceAmount,
            $notes,
        ) {
            if (empty($items)) {
                throw new RuntimeException(
                    'Your order must contain at least one item.'
                );
            }

            if (trim($deliveryAddress) === '') {
                throw new RuntimeException(
                    'A delivery address is required.'
                );
            }

            $deliveryZone = DeliveryZone::query()
                ->whereKey($deliveryZoneId)
                ->where('is_active', true)
                ->first();

            if (! $deliveryZone) {
                throw new RuntimeException(
                    'The selected delivery zone is unavailable.'
                );
            }

            $subtotal = 0;
            $discount = 0;
            $saleItems = [];

            foreach ($items as $item) {
                $product = Product::query()
                    ->whereKey($item['product_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    throw new RuntimeException(
                        'One of the selected products is unavailable.'
                    );
                }

                $quantity = (int) $item['quantity'];

                if ($quantity < 1) {
                    throw new RuntimeException(
                        "Invalid quantity for {$product->name}."
                    );
                }

                if ($product->stock_quantity < $quantity) {
                    throw new RuntimeException(
                        "{$product->name} has only {$product->stock_quantity} item(s) in stock."
                    );
                }

                $originalPrice = (float) $product->selling_price;
                $unitPrice = (float) $product->discounted_price;

                $itemDiscount = max(
                    0,
                    $originalPrice - $unitPrice
                );

                $lineTotal = round(
                    $unitPrice * $quantity,
                    2
                );

                $subtotal += $lineTotal;

                $discount += round(
                    $itemDiscount * $quantity,
                    2
                );

                $saleItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'original_price' => $originalPrice,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $itemDiscount,
                    'line_total' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $discount = round($discount, 2);

            $tax = round(
                $subtotal * 0.16,
                2
            );

            $deliveryFee = (float) $deliveryZone->fee;

            $total = round(
                $subtotal + $tax + $deliveryFee,
                2
            );

            $minimumAdvance = round(
                $total * 0.50,
                2
            );

            if ($advanceAmount < $minimumAdvance) {
                throw new RuntimeException(
                    'A minimum advance payment of KSh '
                    . number_format($minimumAdvance, 2)
                    . ' is required.'
                );
            }

            if ($advanceAmount > $total) {
                throw new RuntimeException(
                    'Advance payment cannot be greater than the order total.'
                );
            }

            $balance = round(
                $total - $advanceAmount,
                2
            );

            $paymentStatus = $balance <= 0
                ? 'paid'
                : 'partially_paid';

            $sale = Sale::create([
                'customer_id' => $customerId,

                'sale_date' => now(),

                'status' => 'Completed',

                'payment_status' => $paymentStatus,
                'payment_method' => 'Online',

                'fulfillment_status' => 'pending',

                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total_amount' => $total,

                'amount_paid' => $advanceAmount,
                'advance_amount' => $advanceAmount,
                'balance_amount' => $balance,
                'change_amount' => 0,

                'delivery_zone_id' => $deliveryZone->id,
                'delivery_address' => trim($deliveryAddress),
                'delivery_fee' => $deliveryFee,

                'notes' => $notes,
            ]);

            foreach ($saleItems as $item) {
                $sale->items()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'original_price' => $item['original_price'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'],
                    'cost_price' => $item['product']->cost_price,
                    'line_total' => $item['line_total'],
                ]);
            }

            /*
             * Customer order is a real sale.
             *
             * Deduct stock immediately.
             */
            $sale->load('items.product');

            $this->inventoryService->validateSaleStock($sale);
            $this->inventoryService->issueSaleStock($sale);

            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
            ]);
        });
    }

    /**
     * Update the fulfillment status of an order.
     *
     * Status flow:
     *
     * pending
     *     ↓
     * preparing
     *     ↓
     * ready
     *     ↓
     * out_for_delivery
     *     ↓
     * delivered
     *
     * Any active order may be cancelled.
     */
    public function updateFulfillmentStatus(
        Sale $sale,
        string $status,
    ): Sale {
        return DB::transaction(function () use (
            $sale,
            $status,
        ) {
            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($sale->id);

            $allowedStatuses = [
                'pending',
                'preparing',
                'ready',
                'out_for_delivery',
                'delivered',
                'cancelled',
            ];

            if (! in_array($status, $allowedStatuses, true)) {
                throw new RuntimeException(
                    'Invalid order status.'
                );
            }

            if (
                in_array(
                    $sale->fulfillment_status,
                    ['delivered', 'cancelled'],
                    true
                )
            ) {
                throw new RuntimeException(
                    'This order can no longer be changed.'
                );
            }

            /*
             * Cancellation is handled separately because
             * it may need to restore inventory.
             */
            if ($status === 'cancelled') {
                throw new RuntimeException(
                    'Use the Cancel Order action to cancel this order.'
                );
            }

            /*
             * Prevent skipping the normal workflow.
             */
            $validTransition = match ($sale->fulfillment_status) {
                'pending' => $status === 'preparing',
                'preparing' => $status === 'ready',
                'ready' => $status === 'out_for_delivery',
                'out_for_delivery' => $status === 'delivered',
                default => false,
            };

            if (! $validTransition) {
                throw new RuntimeException(
                    'Invalid order status transition.'
                );
            }

            $sale->update([
                'fulfillment_status' => $status,
            ]);

            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
            ]);
        });
    }

    /**
     * Cancel an order and restore inventory.
     *
     * A cancellation reason is mandatory.
     */
    public function cancelSale(
        Sale $sale,
        ?string $reason = null,
    ): Sale {
        return DB::transaction(function () use (
            $sale,
            $reason,
        ) {
            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if ($sale->fulfillment_status === 'cancelled') {
                throw new RuntimeException(
                    'This order is already cancelled.'
                );
            }

            if ($sale->fulfillment_status === 'delivered') {
                throw new RuntimeException(
                    'A delivered order cannot be cancelled.'
                );
            }

            $reason = trim((string) $reason);

            if ($reason === '') {
                throw new RuntimeException(
                    'A cancellation reason is required.'
                );
            }

            $sale->loadMissing('items');

            /*
             * Restore stock only when stock was actually
             * deducted for this sale.
             */
            foreach ($sale->items as $item) {

                $issuedQuantity = (int) StockMovement::query()
                    ->where('reference_type', Sale::class)
                    ->where('reference_id', $sale->id)
                    ->where('product_id', $item->product_id)
                    ->where('type', 'OUT')
                    ->sum('quantity');

                /*
                 * Nothing was deducted for this item.
                 */
                if ($issuedQuantity <= 0) {
                    continue;
                }

                /*
                 * Restore only the quantity that was
                 * actually deducted.
                 */
                $restoreQuantity = min(
                    $issuedQuantity,
                    (int) $item->quantity
                );

                $product = Product::query()
                    ->lockForUpdate()
                    ->find($item->product_id);

                if (! $product) {
                    throw new RuntimeException(
                        'Product not found while restoring stock.'
                    );
                }

                Product::query()->whereKey($product->id)->increment(
                    'stock_quantity',
                    $restoreQuantity
                );

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'IN',
                    'quantity' => $restoreQuantity,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'user_id' => Auth::id(),
                    'description' =>
                        "Cancelled order {$sale->invoice_number} - stock restored",
                ]);
            }

            $sale->update([
                'fulfillment_status' => 'cancelled',
                'status' => 'Cancelled',
                'cancellation_reason' => $reason,
            ]);

            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
            ]);
        });
    }
}