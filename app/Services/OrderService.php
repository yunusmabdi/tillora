<?php

namespace App\Services;

use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function __construct(
        protected SalesService $salesService,
    ) {
    }

    /**
     * Create an order from the customer Android app.
     *
     * Laravel remains responsible for:
     * - Product prices
     * - Stock validation
     * - Discounts
     * - Tax
     * - Delivery fee
     * - Total
     * - 50% minimum advance
     * - Payment status
     * - Order status
     */
    public function createOrder(
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

            /*
             |--------------------------------------------------------------------------
             | Basic validation
             |--------------------------------------------------------------------------
             */

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

            /*
             |--------------------------------------------------------------------------
             | Delivery Zone
             |--------------------------------------------------------------------------
             */

            $deliveryZone = DeliveryZone::query()
                ->whereKey($deliveryZoneId)
                ->where('is_active', true)
                ->first();

            if (! $deliveryZone) {
                throw new RuntimeException(
                    'The selected delivery zone is unavailable.'
                );
            }

            /*
             |--------------------------------------------------------------------------
             | Build Sale Items
             |--------------------------------------------------------------------------
             |
             | IMPORTANT:
             | We do not trust prices sent by Android.
             |
             | Prices always come from Laravel's database.
             |
             */

            $subtotal = 0;

            $saleItems = [];

            foreach ($items as $item) {

                if (
                    ! isset($item['product_id']) ||
                    ! isset($item['quantity'])
                ) {
                    throw new RuntimeException(
                        'Each order item must contain a product and quantity.'
                    );
                }

                $product = Product::findOrFail($item['id']);
                
                if (! $product) {
                    throw new RuntimeException(
                        'One of the selected products no longer exists.'
                    );
                }

                $quantity = (int) $item['quantity'];

                if ($quantity < 1) {
                    throw new RuntimeException(
                        "Invalid quantity for {$product->name}."
                    );
                }

                /*
                 |--------------------------------------------------------------------------
                 | Price
                 |--------------------------------------------------------------------------
                 */

                $unitPrice = (float) $product->selling_price;

                $lineTotal = round(
                    $unitPrice * $quantity,
                    2
                );

                $subtotal += $lineTotal;

                $saleItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'original_price' => $unitPrice,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'line_total' => $lineTotal,
                ];
            }

            /*
             |--------------------------------------------------------------------------
             | Discount
             |--------------------------------------------------------------------------
             |
             | Customer discounts/promotions can be added here later.
             |
             */

            $discount = 0;

            /*
             |--------------------------------------------------------------------------
             | Tax
             |--------------------------------------------------------------------------
             */

            $taxableAmount = $subtotal - $discount;

            $tax = round(
                $taxableAmount * 0.16,
                2
            );

            /*
             |--------------------------------------------------------------------------
             | Delivery
             |--------------------------------------------------------------------------
             */

            $deliveryFee = (float) $deliveryZone->fee;

            /*
             |--------------------------------------------------------------------------
             | Final Total
             |--------------------------------------------------------------------------
             */

            $total = round(
                $taxableAmount
                + $tax
                + $deliveryFee,
                2
            );

            /*
             |--------------------------------------------------------------------------
             | Advance Payment
             |--------------------------------------------------------------------------
             */

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

            /*
             |--------------------------------------------------------------------------
             | Remaining Balance
             |--------------------------------------------------------------------------
             */

            $balance = round(
                $total - $advanceAmount,
                2
            );

            /*
             |--------------------------------------------------------------------------
             | Payment Status
             |--------------------------------------------------------------------------
             */

            $paymentStatus = $balance <= 0
                ? 'paid'
                : 'partially_paid';

            /*
             |--------------------------------------------------------------------------
             | Create Sale
             |--------------------------------------------------------------------------
             */

            $sale = Sale::create([
                'customer_id' => $customerId,

                'sale_date' => now(),

                /*
                 * Financial sale is completed.
                 *
                 * Fulfillment is handled separately.
                 */
                'status' => 'Completed',

                'payment_status' => $paymentStatus,

                'payment_method' => 'Online',

                /*
                 * Order requires admin approval.
                 */
                'fulfillment_status' => 'awaiting_approval',

                /*
                 * Financial amounts.
                 */
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total_amount' => $total,

                'amount_paid' => $advanceAmount,
                'advance_amount' => $advanceAmount,
                'balance_amount' => $balance,

                'change_amount' => 0,

                /*
                 * Delivery.
                 */
                'delivery_zone_id' => $deliveryZone->id,
                'delivery_address' => trim($deliveryAddress),
                'delivery_fee' => $deliveryFee,

                'notes' => $notes,
            ]);

            /*
             |--------------------------------------------------------------------------
             | Create Sale Items
             |--------------------------------------------------------------------------
             */

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
             |--------------------------------------------------------------------------
             | Complete Sale / Inventory
             |--------------------------------------------------------------------------
             |
             | Reuse the existing SalesService.
             |
             | This means:
             |
             | Order API
             |      ↓
             | SalesService
             |      ↓
             | InventoryService
             |
             */

            $this->salesService->completeSale($sale);

            /*
             |--------------------------------------------------------------------------
             | Initial Order Status History
             |--------------------------------------------------------------------------
             */

            $sale->statusHistory()->create([
                'status' => 'awaiting_approval',
                'note' => 'Order placed and awaiting admin approval.',
                'updated_by' => null,
            ]);

            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
                'statusHistory',
            ]);
        });
    }
}