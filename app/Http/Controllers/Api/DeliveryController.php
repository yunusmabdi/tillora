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
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
        ]);

        try {

            $result = $deliveryService->calculate(
                (float) $validated['latitude'],
                (float) $validated['longitude']
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