<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;

class DeliveryZoneController extends Controller
{
    /**
     * Get all active delivery zones.
     */
    public function index()
    {
        $zones = DeliveryZone::query()
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

    /**
     * Get a single active delivery zone.
     */
    public function show(DeliveryZone $deliveryZone)
    {
        if (! $deliveryZone->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery zone is not available.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'delivery_zone' => $deliveryZone->only([
                'id',
                'name',
                'description',
                'min_distance',
                'max_distance',
                'fee',
            ]),
        ]);
    }
}