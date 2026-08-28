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
    public function show(Request $request, int $id)
    {
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

    /**
     * Place a new customer order.
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

            'advance_amount' => [
                'required',
                'numeric',
                'min:0',
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
                deliveryAddress: $validated['delivery_address'],
                deliveryZoneId: $validated['delivery_zone_id'],
                advanceAmount: (float) $validated['advance_amount'],
                notes: $validated['notes'] ?? null,
            );

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'order' => $order,
            ], 201);

        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

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
