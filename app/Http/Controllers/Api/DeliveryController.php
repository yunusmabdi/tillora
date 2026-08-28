<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * Calculate the delivery fee for a given distance.
     *
     * The Android app will eventually send an address,
     * which will be converted to a distance before reaching
     * this logic.
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'distance' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);

        $distance = (float) $validated['distance'];

        $zone = DeliveryZone::query()
            ->where('is_active', true)
            ->where('min_distance', '<=', $distance)
            ->where('max_distance', '>=', $distance)
            ->orderBy('min_distance')
            ->first();

        if (! $zone) {
            return response()->json([
                'success' => false,
                'message' => 'No delivery zone is available for this distance.',
            ], 422);
        }

        return response()->json([
            'success' => true,

            'delivery' => [
                'distance' => round($distance, 2),

                'zone' => [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'description' => $zone->description,
                ],

                'fee' => (float) $zone->fee,
            ],
        ]);
    }
}