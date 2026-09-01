<?php

namespace App\Services;

use App\Models\DeliveryZone;
use App\Models\Store;
use InvalidArgumentException;

class DeliveryService
{
    /**
     * Calculate delivery information based on customer coordinates.
     */
    public function calculate(
        float $customerLatitude,
        float $customerLongitude
    ): array {

        // ---------------------------------------------------------
        // Get active store
        // ---------------------------------------------------------

        $store = Store::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->first();

        if (! $store) {
            throw new InvalidArgumentException(
                'No active store with a valid location is configured.'
            );
        }

        // ---------------------------------------------------------
        // Calculate distance
        // ---------------------------------------------------------

        $distance = $this->calculateDistance(
            (float) $store->latitude,
            (float) $store->longitude,
            $customerLatitude,
            $customerLongitude
        );

        // ---------------------------------------------------------
        // Find matching delivery zone
        // ---------------------------------------------------------

        $zone = DeliveryZone::where('is_active', true)
            ->where('min_distance', '<=', $distance)
            ->where('max_distance', '>=', $distance)
            ->first();

        // ---------------------------------------------------------
        // Outside delivery area
        // ---------------------------------------------------------

        if (! $zone) {
            return [
                'deliverable' => false,
                'distance' => round($distance, 2),
                'delivery_fee' => 0,
                'zone' => null,
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                ],
            ];
        }

        // ---------------------------------------------------------
        // Delivery available
        // ---------------------------------------------------------

        return [
            'deliverable' => true,

            'distance' => round($distance, 2),

            'delivery_fee' => (float) $zone->fee,

            'zone' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'min_distance' => (float) $zone->min_distance,
                'max_distance' => (float) $zone->max_distance,
                'fee' => (float) $zone->fee,
            ],

            'store' => [
                'id' => $store->id,
                'name' => $store->name,
            ],
        ];
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     *
     * Result is returned in kilometres.
     */
    private function calculateDistance(
        float $storeLatitude,
        float $storeLongitude,
        float $customerLatitude,
        float $customerLongitude
    ): float {

        $earthRadius = 6371;

        $latitudeDifference = deg2rad(
            $customerLatitude - $storeLatitude
        );

        $longitudeDifference = deg2rad(
            $customerLongitude - $storeLongitude
        );

        $a =
            sin($latitudeDifference / 2) ** 2
            +
            cos(deg2rad($storeLatitude))
            *
            cos(deg2rad($customerLatitude))
            *
            sin($longitudeDifference / 2) ** 2;

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return $earthRadius * $c;
    }
}