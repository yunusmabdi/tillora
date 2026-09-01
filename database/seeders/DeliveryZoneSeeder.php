<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use App\Models\Store;
use Illuminate\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
{
    /**
     * Seed delivery zones around Nairobi CBD.
     *
     * The store is assumed to be located in Nairobi CBD.
     * Delivery fees are fixed per zone.
     */
    public function run(): void
    {
        $store = Store::where('is_active', true)->first();

        if (! $store) {
            $this->command->error(
                'No active store found. Please create an active store first.'
            );

            return;
        }

        $zones = [
            [
                'name' => 'Nairobi CBD',
                'description' => 'Nairobi Central Business District and immediate surroundings.',
                'fee' => 150,
            ],
            [
                'name' => 'Ngara',
                'description' => 'Ngara, Fig Tree and surrounding areas.',
                'fee' => 180,
            ],
            [
                'name' => 'Pangani',
                'description' => 'Pangani and surrounding areas.',
                'fee' => 200,
            ],
            [
                'name' => 'Eastleigh',
                'description' => 'Eastleigh and surrounding areas.',
                'fee' => 220,
            ],
            [
                'name' => 'Westlands',
                'description' => 'Westlands, Muthangari and surrounding areas.',
                'fee' => 250,
            ],
            [
                'name' => 'Parklands',
                'description' => 'Parklands and surrounding areas.',
                'fee' => 250,
            ],
            [
                'name' => 'Kilimani',
                'description' => 'Kilimani, Hurlingham and surrounding areas.',
                'fee' => 250,
            ],
            [
                'name' => 'South B / South C',
                'description' => 'South B, South C and surrounding areas.',
                'fee' => 250,
            ],
            [
                'name' => 'Lavington',
                'description' => 'Lavington and surrounding areas.',
                'fee' => 300,
            ],
            [
                'name' => 'Lang’ata',
                'description' => 'Lang’ata and surrounding areas.',
                'fee' => 300,
            ],
            [
                'name' => 'Kasarani',
                'description' => 'Kasarani and surrounding areas.',
                'fee' => 300,
            ],
            [
                'name' => 'Roysambu',
                'description' => 'Roysambu, TRM and surrounding areas.',
                'fee' => 300,
            ],
            [
                'name' => 'Karen',
                'description' => 'Karen and surrounding areas.',
                'fee' => 400,
            ],
            [
                'name' => 'Embakasi',
                'description' => 'Embakasi and surrounding areas.',
                'fee' => 350,
            ],
            [
                'name' => 'Donholm',
                'description' => 'Donholm and surrounding areas.',
                'fee' => 300,
            ],
            [
                'name' => 'Ruaka',
                'description' => 'Ruaka and surrounding areas.',
                'fee' => 350,
            ],
            [
                'name' => 'Runda',
                'description' => 'Runda and surrounding areas.',
                'fee' => 400,
            ],
        ];

        foreach ($zones as $zone) {
            DeliveryZone::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'name' => $zone['name'],
                ],
                [
                    'description' => $zone['description'],
                    'fee' => $zone['fee'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info(
            count($zones) . ' Nairobi delivery zones seeded successfully.'
        );
    }
}