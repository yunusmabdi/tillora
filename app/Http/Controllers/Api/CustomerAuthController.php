<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    /**
     * Register a new customer.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $token = $customer->createToken('android-app')->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'customer' => $customer,
            'token' => $token,
        ], 201);
    }

    /**
    * Login an existing customer.
    */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = Customer::where('email', '=', $validated['email'], 'and')->first();

        if (! $customer || ! Hash::check($validated['password'], $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $customer->is_active) {
            return response()->json([
                'message' => 'Your account is currently inactive.',
            ], 403);
        }

        $token = $customer->createToken('android-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'customer' => $customer,
            'token' => $token,
        ]);
    }
    /**
     * Get the currently authenticated customer.
     */
    public function me(Request $request)
    {
        return response()->json([
            'customer' => $request->user(),
        ]);
    }

    /**
     * Logout the current customer.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}