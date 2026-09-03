<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class DriverAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', '=', $validated['email'], 'and')
            ->first();

        if (
            ! $user ||
            ! Hash::check($validated['password'], $user->password)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (! $user->hasRole('Rider')) {
            return response()->json([
                'success' => false,
                'message' => 'This account is not a driver account.',
            ], 403);
        }

        $driver = $user->driver;

        if (! $driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver profile not found.',
            ], 403);
        }

        if ($driver->status === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => 'Driver account is inactive.',
            ], 403);
        }

        $token = $user->createToken('driver-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Driver login successful.',
            'token' => $token,
            'driver' => $driver,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}