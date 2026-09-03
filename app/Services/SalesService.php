<?php

namespace App\Services;

use App\Models\DeliveryZone;
use App\Models\Driver;
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
     * Order starts as Draft.
     *
     * Payment has NOT been confirmed.
     *
     * Stock is NOT deducted until the first
     * successful payment.
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
                 * Lock product while validating stock.
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
                 * Check stock.
                 *
                 * Stock is NOT deducted here.
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

                    'product_id' =>
                        $product->id,

                    'quantity' =>
                        $quantity,

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
             * Round totals.
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

            /*
             * Delivery fee.
             */
            $deliveryFee = round(
                (float) $deliveryZone->fee,
                2
            );

            /*
             * Final order total.
             */
            $total = round(
                $subtotal
                + $tax
                + $deliveryFee,
                2
            );

            /*
             * Determine initial payment requirement.
             *
             * full:
             * 100%
             *
             * advance:
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
             * Payment has not happened.
             *
             * Stock has not been deducted.
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
    | CUSTOMER PAYMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Confirm a successful customer payment.
     *
     * Inventory is issued ONLY ONCE.
     *
     * Example:
     *
     * First payment:
     * 50%
     *
     * Second payment:
     * remaining 50%
     *
     * Inventory is only deducted during the
     * first successful payment.
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
             * Lock the order.
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

            if (
                $sale->fulfillment_status ===
                'delivered'
            ) {
                throw new RuntimeException(
                    'A delivered order cannot receive payment.'
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

            $paymentMethod =
                strtolower(
                    trim($paymentMethod)
                );

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
             * Transaction reference required.
             */

            $transactionReference =
                trim(
                    (string) $transactionReference
                );

            if ($transactionReference === '') {
                throw new RuntimeException(
                    'A payment transaction reference is required.'
                );
            }

            /*
             * Existing payment.
             */

            $alreadyPaid = round(
                (float) $sale->amount_paid,
                2
            );

            /*
             * Remaining balance before this payment.
             */

            $remainingBalance = round(
                (float) $sale->total_amount
                - $alreadyPaid,
                2
            );

            /*
             * Prevent overpayment.
             */

            if (
                $amountPaid >
                $remainingBalance
            ) {
                throw new RuntimeException(
                    'Payment cannot exceed the remaining order balance.'
                );
            }

            /*
             * Calculate new totals.
             */

            $newAmountPaid = round(
                $alreadyPaid
                + $amountPaid,
                2
            );

            $balance = round(
                (float) $sale->total_amount
                - $newAmountPaid,
                2
            );

            /*
             * Determine payment status.
             */

            $paymentStatus =
                $balance <= 0
                    ? 'paid'
                    : 'partially_paid';

            /*
             * Preserve the first payment
             * as the advance amount.
             */
            $advanceAmount =
                $alreadyPaid > 0
                    ? (float) $sale->advance_amount
                    : $amountPaid;

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
                    $transactionReference,

                'amount_paid' =>
                    $newAmountPaid,

                'advance_amount' =>
                    $advanceAmount,

                'balance_amount' =>
                    $balance,

                'change_amount' =>
                    0,

                /*
                 * First successful payment
                 * starts preparation.
                 *
                 * Do not reset an order already
                 * being processed.
                 */
                'fulfillment_status' =>
                    $sale->fulfillment_status === 'pending'
                        ? 'preparing'
                        : $sale->fulfillment_status,
            ]);

            /*
             * CRITICAL:
             *
             * Deduct stock only once.
             */
            if (! $this->stockHasBeenIssued($sale)) {

                $this->inventoryService
                    ->validateSaleStock($sale);

                $this->inventoryService
                    ->issueSaleStock($sale);
            }

            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
                'driver',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether stock has already
     * been issued for this sale.
     */
    protected function stockHasBeenIssued(
        Sale $sale
    ): bool {

        return StockMovement::query()
            ->where(
                'reference_type',
                Sale::class
            )
            ->where(
                'reference_id',
                $sale->id
            )
            ->where(
                'type',
                'OUT'
            )
            ->exists();
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

            if (
                $sale->payment_status ===
                'paid'
            ) {
                throw new RuntimeException(
                    'A paid order cannot be marked as failed.'
                );
            }

            if (
                $sale->fulfillment_status ===
                'cancelled'
            ) {
                throw new RuntimeException(
                    'A cancelled order cannot have a payment attempt.'
                );
            }

            $sale->update([
                'payment_status' => 'failed',
            ]);

            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
                'driver',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN FULFILLMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Update fulfillment status from the
     * administrative side.
     *
     * ADMIN FLOW:
     *
     * pending
     *     ↓
     * preparing
     *     ↓
     * ready
     *
     * Rider-controlled statuses cannot be
     * changed through this method.
     */
    public function updateFulfillmentStatus(
        Sale $sale,
        string $status,
    ): Sale {

        return DB::transaction(function () use (
            $sale,
            $status,
        ) {

            /*
             * Lock order.
             */
            $sale = Sale::query()
                ->lockForUpdate()
                ->with('driver')
                ->findOrFail($sale->id);

            /*
             * Admin-controlled statuses only.
             */
            $allowedStatuses = [
                'pending',
                'preparing',
                'ready',
            ];

            if (! in_array(
                $status,
                $allowedStatuses,
                true
            )) {

                throw new RuntimeException(
                    'This status can only be controlled by the assigned rider.'
                );
            }

            /*
             * Final states cannot be changed.
             */
            if (in_array(
                $sale->fulfillment_status,
                [
                    'delivered',
                    'cancelled',
                ],
                true
            )) {

                throw new RuntimeException(
                    'This order can no longer be changed.'
                );
            }

            /*
             * Validate admin transition.
             */
            $validTransition =
                match (
                    $sale->fulfillment_status
                ) {

                    'pending' =>
                        $status === 'preparing',

                    'preparing' =>
                        $status === 'ready',

                    default =>
                        false,
                };

            if (! $validTransition) {

                throw new RuntimeException(
                    'Invalid order status transition.'
                );
            }

            /*
             * Update order.
             */
            $sale->update([
                'fulfillment_status' =>
                    $status,
            ]);

            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
                'driver',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RIDER ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Assign an available rider to a ready order.
     *
     * Rider becomes busy immediately.
     */
    public function assignDriver(
        Sale $sale,
        int $driverId,
    ): Sale {

        return DB::transaction(function () use (
            $sale,
            $driverId,
        ) {

            /*
             * Lock the order.
             */
            $sale = Sale::query()
                ->lockForUpdate()
                ->with('driver')
                ->findOrFail($sale->id);

            /*
             * Order must be ready.
             */
            if (
                $sale->fulfillment_status !==
                'ready'
            ) {

                throw new RuntimeException(
                    'A rider can only be assigned when the order is ready.'
                );
            }

            /*
             * Prevent duplicate assignment.
             */
            if ($sale->driver_id) {

                throw new RuntimeException(
                    'A rider is already assigned to this order.'
                );
            }

            /*
             * Lock rider.
             */
            $driver = Driver::query()
                ->lockForUpdate()
                ->find($driverId);

            if (! $driver) {

                throw new RuntimeException(
                    'The selected rider does not exist.'
                );
            }

            /*
             * Rider must be available.
             */
            if (
                $driver->status !==
                'available'
            ) {

                throw new RuntimeException(
                    'The selected rider is not available.'
                );
            }

            /*
             * Assign rider.
             */
            $sale->update([
                'driver_id' =>
                    $driver->id,
            ]);

            /*
             * Rider becomes busy.
             */
            $driver->update([
                'status' =>
                    'busy',
            ]);

            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
                'driver',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RIDER PICKUP
    |--------------------------------------------------------------------------
    */

    /**
     * Rider picks up an assigned order.
     *
     * READY
     *   ↓
     * PICKED UP
     */
    public function riderPickupOrder(
        Sale $sale,
        Driver $driver,
    ): Sale {

        return DB::transaction(function () use (
            $sale,
            $driver,
        ) {

            /*
             * Lock order.
             */
            $sale = Sale::query()
                ->lockForUpdate()
                ->with('driver')
                ->findOrFail($sale->id);

            /*
             * Verify rider assignment.
             */
            if (
                $sale->driver_id !==
                $driver->id
            ) {

                throw new RuntimeException(
                    'This order is not assigned to you.'
                );
            }

            /*
             * Order must be ready.
             */
            if (
                $sale->fulfillment_status !==
                'ready'
            ) {

                throw new RuntimeException(
                    'This order is not ready for pickup.'
                );
            }

            /*
             * Rider must be busy.
             */
            if (
                $driver->status !==
                'busy'
            ) {

                throw new RuntimeException(
                    'You are not currently assigned to an active delivery.'
                );
            }

            /*
             * Update status.
             */
            $sale->update([
                'fulfillment_status' =>
                    'picked_up',
            ]);

            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
                'driver',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RIDER START DELIVERY
    |--------------------------------------------------------------------------
    */

    /**
     * Rider starts delivery.
     *
     * PICKED UP
     *     ↓
     * OUT FOR DELIVERY
     */
    public function riderStartDelivery(
        Sale $sale,
        Driver $driver,
    ): Sale {

        return DB::transaction(function () use (
            $sale,
            $driver,
        ) {

            /*
             * Lock order.
             */
            $sale = Sale::query()
                ->lockForUpdate()
                ->with('driver')
                ->findOrFail($sale->id);

            /*
             * Verify rider assignment.
             */
            if (
                $sale->driver_id !==
                $driver->id
            ) {

                throw new RuntimeException(
                    'This order is not assigned to you.'
                );
            }

            /*
             * Must be picked up first.
             */
            if (
                $sale->fulfillment_status !==
                'picked_up'
            ) {

                throw new RuntimeException(
                    'The order must be picked up before starting delivery.'
                );
            }

            /*
             * Rider must still be busy.
             */
            if (
                $driver->status !==
                'busy'
            ) {

                throw new RuntimeException(
                    'You are not currently assigned to an active delivery.'
                );
            }

            /*
             * Update status.
             */
            $sale->update([
                'fulfillment_status' =>
                    'out_for_delivery',
            ]);

            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
                'driver',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RIDER DELIVERY
    |--------------------------------------------------------------------------
    */

    /**
     * Complete delivery.
     *
     * OUT FOR DELIVERY
     *        ↓
     *     DELIVERED
     *
     * Full payment is mandatory.
     */
    public function riderDeliverOrder(
        Sale $sale,
        Driver $driver,
    ): Sale {

        return DB::transaction(function () use (
            $sale,
            $driver,
        ) {

            /*
             * Lock order.
             */
            $sale = Sale::query()
                ->lockForUpdate()
                ->with('driver')
                ->findOrFail($sale->id);

            /*
             * Verify rider assignment.
             */
            if (
                $sale->driver_id !==
                $driver->id
            ) {

                throw new RuntimeException(
                    'This order is not assigned to you.'
                );
            }

            /*
             * Must be out for delivery.
             */
            if (
                $sale->fulfillment_status !==
                'out_for_delivery'
            ) {

                throw new RuntimeException(
                    'This order is not currently out for delivery.'
                );
            }

            /*
             * CRITICAL PAYMENT RULE.
             *
             * Delivery cannot be completed
             * while there is an outstanding balance.
             */
            if (
                $sale->payment_status !==
                'paid'
                ||
                (float) $sale->balance_amount > 0
            ) {

                throw new RuntimeException(
                    'This order cannot be delivered until the full payment has been received.'
                );
            }

            /*
             * Mark delivered.
             */
            $sale->update([
                'fulfillment_status' =>
                    'delivered',
            ]);

            /*
             * Rider becomes available again.
             */
            $driver->update([
                'status' =>
                    'available',
            ]);

            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
                'driver',
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
     *
     * If a rider was assigned, the rider
     * becomes available again.
     */
    public function cancelSale(
        Sale $sale,
        ?string $reason = null,
    ): Sale {

        return DB::transaction(function () use (
            $sale,
            $reason,
        ) {

            /*
             * Lock order.
             */
            $sale = Sale::query()
                ->lockForUpdate()
                ->with('driver')
                ->findOrFail($sale->id);

            /*
             * Already cancelled.
             */
            if (
                $sale->fulfillment_status ===
                'cancelled'
            ) {

                throw new RuntimeException(
                    'This order is already cancelled.'
                );
            }

            /*
             * Delivered orders cannot be cancelled.
             */
            if (
                $sale->fulfillment_status ===
                'delivered'
            ) {

                throw new RuntimeException(
                    'A delivered order cannot be cancelled.'
                );
            }

            /*
             * Cancellation reason required.
             */
            $reason = trim(
                (string) $reason
            );

            if ($reason === '') {

                throw new RuntimeException(
                    'A cancellation reason is required.'
                );
            }

            /*
             * Load items.
             */
            $sale->loadMissing('items');

            /*
             * Restore stock only if it was issued.
             */
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

                /*
                 * No stock was issued.
                 */
                if ($issuedQuantity <= 0) {
                    continue;
                }

                /*
                 * Never restore more than
                 * the original ordered quantity.
                 */
                $restoreQuantity = min(
                    $issuedQuantity,
                    (int) $item->quantity
                );

                /*
                 * Lock product.
                 */
                $product = Product::query()
                    ->lockForUpdate()
                    ->find(
                        $item->product_id
                    );

                if (! $product) {

                    throw new RuntimeException(
                        'Product not found while restoring stock.'
                    );
                }

                /*
                 * Restore inventory.
                 */
                Product::query()
                    ->whereKey($product->id)
                    ->increment(
                        'stock_quantity',
                        $restoreQuantity
                    );

                /*
                 * Record stock movement.
                 */
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

            /*
             * Cancel order.
             */
            $sale->update([

                'fulfillment_status' =>
                    'cancelled',

                'status' =>
                    'Cancelled',

                'cancellation_reason' =>
                    $reason,
            ]);

            /*
             * Release rider.
             */
            if ($sale->driver) {

                $sale->driver->update([
                    'status' =>
                        'available',
                ]);
            }

            return $sale->fresh([
                'customer',
                'items.product',
                'deliveryZone',
                'driver',
            ]);
        });
    }
}