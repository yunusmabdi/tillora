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

    /*
    |--------------------------------------------------------------------------
    | POS SALE
    |--------------------------------------------------------------------------
    */

    /**
     * Complete a POS sale and deduct inventory.
     */
    public function completeSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {

            $sale->loadMissing('items.product');

            $this->inventoryService
                ->validateSaleStock($sale);

            $this->inventoryService
                ->issueSaleStock($sale);

            $sale->update([
                'status' => 'Completed',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER ORDER
    |--------------------------------------------------------------------------
    */

    /**
     * Create a customer order.
     *
     * IMPORTANT:
     *
     * The order is created as Draft.
     *
     * Payment has NOT been confirmed.
     *
     * Stock is NOT deducted.
     */
    public function createCustomerOrder(
        int $customerId,
        array $items,
        string $deliveryAddress,
        int $deliveryZoneId,
        string $paymentOption,
        ?string $notes = null,
    ): Sale {

        return DB::transaction(function () use (
            $customerId,
            $items,
            $deliveryAddress,
            $deliveryZoneId,
            $paymentOption,
            $notes,
        ) {

            /*
             * Validate basic information.
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

            if (! in_array(
                $paymentOption,
                ['full', 'advance'],
                true
            )) {

                throw new RuntimeException(
                    'Invalid payment option.'
                );
            }


            /*
             * Validate delivery zone.
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
             * Calculate order totals.
             */

            $subtotal = 0;
            $discount = 0;

            $saleItems = [];


            foreach ($items as $item) {

                /*
                 * Lock product row.
                 *
                 * This prevents two checkout requests from
                 * changing the product while we're validating it.
                 */

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


                /*
                 * Validate stock.
                 *
                 * IMPORTANT:
                 *
                 * We only CHECK stock here.
                 *
                 * We do NOT deduct it yet.
                 */

                if ($product->stock_quantity < $quantity) {

                    throw new RuntimeException(
                        "{$product->name} has only "
                        . "{$product->stock_quantity} item(s) in stock."
                    );
                }


                /*
                 * NEVER trust prices coming from Android.
                 */

                $originalPrice =
                    (float) $product->selling_price;

                $unitPrice =
                    (float) $product->discounted_price;


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
                    'product_id' => $product->id,

                    'quantity' => $quantity,

                    'original_price' =>
                        $originalPrice,

                    'unit_price' =>
                        $unitPrice,

                    'discount_amount' =>
                        $itemDiscount,

                    'cost_price' =>
                        (float) $product->cost_price,

                    'line_total' =>
                        $lineTotal,
                ];
            }


            /*
             * Final totals.
             */

            $subtotal = round(
                $subtotal,
                2
            );

            $discount = round(
                $discount,
                2
            );


            /*
             * Tillora tax = 16%.
             */

            $tax = round(
                $subtotal * 0.16,
                2
            );


            $deliveryFee = round(
                (float) $deliveryZone->fee,
                2
            );


            $total = round(
                $subtotal
                + $tax
                + $deliveryFee,
                2
            );


            /*
             * Determine required payment.
             *
             * FULL:
             * 100%
             *
             * ADVANCE:
             * 50%
             */

            $requiredPayment =
                $paymentOption === 'full'
                    ? $total
                    : round(
                        $total * 0.50,
                        2
                    );


            $balance = round(
                $total - $requiredPayment,
                2
            );


            /*
             * Create Draft order.
             *
             * No payment has happened yet.
             *
             * No stock has been deducted.
             */

            $sale = Sale::create([

                'customer_id' =>
                    $customerId,

                'sale_date' =>
                    now()->toDateString(),

                'status' =>
                    'Draft',

                'payment_status' =>
                    'pending',

                'payment_method' =>
                    'Online',

                'transaction_reference' =>
                    null,

                'fulfillment_status' =>
                    'pending',

                'subtotal' =>
                    $subtotal,

                'discount' =>
                    $discount,

                'discount_amount' =>
                    $discount,

                'tax' =>
                    $tax,

                'total_amount' =>
                    $total,

                /*
                 * IMPORTANT:
                 *
                 * These remain zero until payment
                 * is successfully confirmed.
                 */

                'amount_paid' =>
                    0,

                'advance_amount' =>
                    0,

                'balance_amount' =>
                    $total,

                'change_amount' =>
                    0,

                'delivery_zone_id' =>
                    $deliveryZone->id,

                'delivery_address' =>
                    trim($deliveryAddress),

                'delivery_fee' =>
                    $deliveryFee,

                'notes' =>
                    $notes,
            ]);


            /*
             * Create sale items.
             */

            foreach ($saleItems as $item) {

                $sale->items()->create(
                    $item
                );
            }


            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRM CUSTOMER PAYMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Confirm a successful customer payment.
     *
     * This is the ONLY point where customer-order
     * inventory is deducted.
     */
    public function confirmCustomerPayment(
        Sale $sale,
        float $amountPaid,
        string $paymentMethod,
        ?string $transactionReference = null,
    ): Sale {

        return DB::transaction(function () use (
            $sale,
            $amountPaid,
            $paymentMethod,
            $transactionReference,
        ) {

            /*
             * Lock order.
             */

            $sale = Sale::query()
                ->lockForUpdate()
                ->with('items.product')
                ->findOrFail($sale->id);


            /*
             * Validate order state.
             */

            if ($sale->payment_status === 'paid') {

                throw new RuntimeException(
                    'This order has already been fully paid.'
                );
            }


            if (
                $sale->fulfillment_status ===
                'cancelled'
            ) {

                throw new RuntimeException(
                    'A cancelled order cannot receive payment.'
                );
            }


            /*
             * Validate payment amount.
             */

            if ($amountPaid <= 0) {

                throw new RuntimeException(
                    'Payment amount must be greater than zero.'
                );
            }


            /*
             * Validate payment method.
             */

            if (! in_array(
                $paymentMethod,
                ['mpesa', 'card'],
                true
            )) {

                throw new RuntimeException(
                    'Invalid payment method.'
                );
            }


            /*
             * Transaction reference is mandatory.
             */

            if (
                trim(
                    (string) $transactionReference
                ) === ''
            ) {

                throw new RuntimeException(
                    'A payment transaction reference is required.'
                );
            }


            /*
             * Calculate new payment total.
             */

            $alreadyPaid =
                (float) $sale->amount_paid;


            $newAmountPaid = round(
                $alreadyPaid + $amountPaid,
                2
            );


            /*
             * Prevent overpayment.
             */

            if (
                $newAmountPaid >
                (float) $sale->total_amount
            ) {

                throw new RuntimeException(
                    'Payment cannot exceed the order total.'
                );
            }


            $balance = round(
                (float) $sale->total_amount
                - $newAmountPaid,
                2
            );


            $paymentStatus =
                $balance <= 0
                    ? 'paid'
                    : 'partially_paid';


            /*
             * Update payment.
             */

            $sale->update([

                'status' =>
                    'Completed',

                'payment_status' =>
                    $paymentStatus,

                'payment_method' =>
                    strtoupper($paymentMethod),

                'transaction_reference' =>
                    trim($transactionReference),

                'amount_paid' =>
                    $newAmountPaid,

                /*
                 * First payment is the advance.
                 */

                'advance_amount' =>
                    $alreadyPaid > 0
                        ? $alreadyPaid
                        : $amountPaid,

                'balance_amount' =>
                    $balance,

                'change_amount' =>
                    0,

                /*
                 * Successful payment starts
                 * fulfillment.
                 */

                'fulfillment_status' =>
                    'preparing',
            ]);


            /*
             * NOW deduct inventory.
             */

            $this->inventoryService
                ->validateSaleStock($sale);

            $this->inventoryService
                ->issueSaleStock($sale);


            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENT FAILED
    |--------------------------------------------------------------------------
    */

    /**
     * Mark payment attempt as failed.
     */
    public function markPaymentFailed(
        Sale $sale,
    ): Sale {

        return DB::transaction(function () use ($sale) {

            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($sale->id);


            if ($sale->payment_status === 'paid') {

                throw new RuntimeException(
                    'A paid order cannot be marked as failed.'
                );
            }


            $sale->update([
                'payment_status' => 'failed',
            ]);


            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | FULFILLMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Update fulfillment status.
     *
     * Flow:
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


            if (! in_array(
                $status,
                $allowedStatuses,
                true
            )) {

                throw new RuntimeException(
                    'Invalid order status.'
                );
            }


            if (
                in_array(
                    $sale->fulfillment_status,
                    [
                        'delivered',
                        'cancelled',
                    ],
                    true
                )
            ) {

                throw new RuntimeException(
                    'This order can no longer be changed.'
                );
            }


            if ($status === 'cancelled') {

                throw new RuntimeException(
                    'Use the Cancel Order action to cancel this order.'
                );
            }


            $validTransition =
                match ($sale->fulfillment_status) {

                    'pending' =>
                        $status === 'preparing',

                    'preparing' =>
                        $status === 'ready',

                    'ready' =>
                        $status === 'out_for_delivery',

                    'out_for_delivery' =>
                        $status === 'delivered',

                    default =>
                        false,
                };


            if (! $validTransition) {

                throw new RuntimeException(
                    'Invalid order status transition.'
                );
            }


            $sale->update([
                'fulfillment_status' =>
                    $status,
            ]);


            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL ORDER
    |--------------------------------------------------------------------------
    */

    /**
     * Cancel an order and restore inventory.
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


            if (
                $sale->fulfillment_status ===
                'cancelled'
            ) {

                throw new RuntimeException(
                    'This order is already cancelled.'
                );
            }


            if (
                $sale->fulfillment_status ===
                'delivered'
            ) {

                throw new RuntimeException(
                    'A delivered order cannot be cancelled.'
                );
            }


            $reason = trim(
                (string) $reason
            );


            if ($reason === '') {

                throw new RuntimeException(
                    'A cancellation reason is required.'
                );
            }


            $sale->loadMissing('items');


            foreach ($sale->items as $item) {

                $issuedQuantity =
                    (int) StockMovement::query()
                        ->where(
                            'reference_type',
                            Sale::class
                        )
                        ->where(
                            'reference_id',
                            $sale->id
                        )
                        ->where(
                            'product_id',
                            $item->product_id
                        )
                        ->where(
                            'type',
                            'OUT'
                        )
                        ->sum('quantity');


                if ($issuedQuantity <= 0) {
                    continue;
                }


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


                Product::query()
                    ->whereKey($product->id)
                    ->increment(
                        'stock_quantity',
                        $restoreQuantity
                    );


                StockMovement::create([

                    'product_id' =>
                        $product->id,

                    'type' =>
                        'IN',

                    'quantity' =>
                        $restoreQuantity,

                    'reference_type' =>
                        Sale::class,

                    'reference_id' =>
                        $sale->id,

                    'user_id' =>
                        Auth::id(),

                    'description' =>
                        "Cancelled order "
                        . "{$sale->invoice_number}"
                        . " - stock restored",
                ]);
            }


            $sale->update([

                'fulfillment_status' =>
                    'cancelled',

                'status' =>
                    'Cancelled',

                'cancellation_reason' =>
                    $reason,
            ]);


            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
            ]);
        });
    }
}