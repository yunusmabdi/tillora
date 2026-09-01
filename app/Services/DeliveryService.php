<?php

namespace App\Services;

use App\Models\DeliveryZone;
use InvalidArgumentException;

class DeliveryService
{
    /**
     * Get delivery information for a selected delivery zone.
     */
    public function calculate(int $deliveryZoneId): array
    {
        $zone = DeliveryZone::where('id', $deliveryZoneId)
            ->where('is_active', true)
            ->first();

        if (! $zone) {
            throw new InvalidArgumentException(
                'The selected delivery zone is not available.'
            );
        }

        return [
            'deliverable' => true,

            'delivery_fee' => (float) $zone->fee,

            'zone' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'description' => $zone->description,
                'fee' => (float) $zone->fee,
            ],
        ];
    }
}