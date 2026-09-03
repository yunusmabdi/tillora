<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Sale;
use App\Services\SalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class RiderController extends Controller
{
    public function __construct(
        protected SalesService $salesService,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | RIDER ORDERS
    |--------------------------------------------------------------------------
    */

    /**
     * Get orders assigned to the authenticated rider.
     */
    public function orders(Request $request): JsonResponse
    {
        $driver = $this->getAuthenticatedDriver($request);

        $orders = Sale::query()
            ->with([
                'customer',
                'items.product',
                'deliveryZone',
                'driver',
            ])
            ->where('driver_id', $driver->id)
            ->whereNotIn('fulfillment_status', [
                'delivered',
                'cancelled',
            ])
            ->latest('sale_date')
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SINGLE ORDER
    |--------------------------------------------------------------------------
    */

    /**
     * Get one order assigned to the authenticated rider.
     */
    public function show(
        Request $request,
        Sale $sale,
    ): JsonResponse {

        $driver = $this->getAuthenticatedDriver($request);

        $this->ensureAssignedToDriver(
            $sale,
            $driver
        );

        $sale->load([
            'customer',
            'items.product',
            'deliveryZone',
            'driver',
        ]);

        return response()->json([
            'success' => true,
            'data' => $sale,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PICKUP
    |--------------------------------------------------------------------------
    */

    /**
     * READY → PICKED UP
     */
    public function pickup(
        Request $request,
        Sale $sale,
    ): JsonResponse {

        try {

            $driver = $this->getAuthenticatedDriver($request);

            $sale = $this->salesService
                ->riderPickupOrder(
                    $sale,
                    $driver
                );

            return response()->json([
                'success' => true,
                'message' => 'Order picked up successfully.',
                'data' => $sale,
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | START DELIVERY
    |--------------------------------------------------------------------------
    */

    /**
     * PICKED UP → OUT FOR DELIVERY
     */
    public function startDelivery(
        Request $request,
        Sale $sale,
    ): JsonResponse {

        try {

            $driver = $this->getAuthenticatedDriver($request);

            $sale = $this->salesService
                ->riderStartDelivery(
                    $sale,
                    $driver
                );

            return response()->json([
                'success' => true,
                'message' => 'Delivery started successfully.',
                'data' => $sale,
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELIVER
    |--------------------------------------------------------------------------
    */

    /**
     * OUT FOR DELIVERY → DELIVERED
     */
    public function deliver(
        Request $request,
        Sale $sale,
    ): JsonResponse {

        try {

            $driver = $this->getAuthenticatedDriver($request);

            $sale = $this->salesService
                ->riderDeliverOrder(
                    $sale,
                    $driver
                );

            return response()->json([
                'success' => true,
                'message' => 'Order delivered successfully.',
                'data' => $sale,
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED RIDER
    |--------------------------------------------------------------------------
    */

    /**
     * Resolve the authenticated rider.
     *
     * For now this expects the authenticated user
     * to have a driver_id attribute.
     */
    protected function getAuthenticatedDriver(
        Request $request,
    ): Driver {

        $user = $request->user();

        abort_unless(
            $user,
            401,
            'Unauthenticated.'
        );

        $driver = $user->driver;

        abort_unless(
            $driver !== null,
            403,
            'This account is not linked to a rider.'
        );

        $driver = Driver::query()
            ->whereKey($driver->id)
            ->where('status', '!=', 'inactive')
            ->first();

        abort_unless(
            $driver !== null,
            403,
            'Rider account is inactive.'
        );

        return $driver;
    }

    /*
    |--------------------------------------------------------------------------
    | ORDER OWNERSHIP
    |--------------------------------------------------------------------------
    */

    protected function ensureAssignedToDriver(
        Sale $sale,
        Driver $driver,
    ): void {

        abort_unless(
            $sale->driver_id === $driver->id,
            403,
            'This order is not assigned to you.'
        );
    }
    public function me(Request $request): JsonResponse
    {
        $driver = $this->getAuthenticatedDriver($request);

        $driver->load('user');

        return response()->json([
            'success' => true,
            'data' => $driver,
        ]);
    }
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => $user->notifications()
                ->latest()
                ->get(),
        ]);
    }

    public function unreadNotifications(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => $user->unreadNotifications()
                ->latest()
                ->get(),
        ]);
    }
    public function markNotificationAsRead(Request $request, string $id ): JsonResponse 
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }
}