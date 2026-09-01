<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class DeliveryController extends Controller
{
    public function calculate(
        Request $request,
        DeliveryService $deliveryService
    ): JsonResponse {

        $validated = $request->validate([
            'delivery_zone_id' => [
                'required',
                'integer',
                'exists:delivery_zones,id',
            ],
        ]);

        try {

            $result = $deliveryService->calculate(
                (int) $validated['delivery_zone_id']
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (InvalidArgumentException $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}