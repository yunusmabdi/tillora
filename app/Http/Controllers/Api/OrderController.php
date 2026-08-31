<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SalesService;
use Illuminate\Http\Request;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(
        protected SalesService $salesService,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER ORDERS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the customer's orders.
     */
    public function index(Request $request)
    {
        $customer = $request->user();

        $orders = $customer->sales()
            ->with([
                'items.product',
                'deliveryZone',
            ])
            ->latest('sale_date')
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    /**
     * Get a single customer order.
     */
    public function show(
        Request $request,
        int $id
    ) {
        $customer = $request->user();

        $order = $customer->sales()
            ->with([
                'items.product',
                'deliveryZone',
            ])
            ->find($id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new customer order.
     *
     * IMPORTANT:
     *
     * This does NOT confirm payment.
     * This does NOT deduct stock.
     *
     * It creates a Draft order which must be paid
     * through the payment endpoint.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'delivery_address' => [
                'required',
                'string',
                'max:1000',
            ],

            'delivery_zone_id' => [
                'required',
                'integer',
                'exists:delivery_zones,id',
            ],

            'payment_option' => [
                'required',
                'in:full,advance',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        try {

            $order = $this->salesService->createCustomerOrder(
                customerId: $request->user()->id,

                items: $validated['items'],

                deliveryAddress:
                    $validated['delivery_address'],

                deliveryZoneId:
                    $validated['delivery_zone_id'],

                paymentOption:
                    $validated['payment_option'],

                notes:
                    $validated['notes'] ?? null,
            );

            return response()->json([
                'success' => true,

                'message' =>
                    'Order created. Proceed to payment.',

                'order' => $order,
            ], 201);

        } catch (RuntimeException $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Confirm a successful customer payment.
     *
     * This is the point where:
     *
     * 1. Payment is recorded
     * 2. Order becomes Completed
     * 3. Stock is deducted
     * 4. Fulfillment becomes Preparing
     */
    public function confirmPayment(
        Request $request,
        int $id
    ) {
        $validated = $request->validate([
            'amount_paid' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'payment_method' => [
                'required',
                'in:mpesa,card',
            ],

            'transaction_reference' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $customer = $request->user();

        /*
         * Customers can only pay for their own orders.
         */
        $order = $customer->sales()
            ->find($id);

        if (! $order) {

            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        try {

            $order = $this->salesService->confirmCustomerPayment(
                sale: $order,

                amountPaid:
                    (float) $validated['amount_paid'],

                paymentMethod:
                    $validated['payment_method'],

                transactionReference:
                    $validated['transaction_reference'],
            );

            return response()->json([
                'success' => true,

                'message' =>
                    'Payment confirmed successfully.',

                'order' => $order,
            ]);

        } catch (RuntimeException $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELIVERY ZONES
    |--------------------------------------------------------------------------
    */

    /**
     * Get available delivery zones.
     */
    public function deliveryZones()
    {
        $zones = \App\Models\DeliveryZone::query()
            ->where('is_active', true)
            ->orderBy('min_distance')
            ->get([
                'id',
                'name',
                'description',
                'min_distance',
                'max_distance',
                'fee',
            ]);

        return response()->json([
            'success' => true,
            'delivery_zones' => $zones,
        ]);
    }
}