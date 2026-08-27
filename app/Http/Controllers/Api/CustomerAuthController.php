<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    /**
     * Send an email verification OTP.
     */
    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = strtolower(trim($validated['email']));

        // Check if the email is already registered
        if (Customer::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['An account with this email already exists.'],
            ]);
        }

        // Generate a 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        // Store registration details temporarily.
        // The OTP and password are stored as hashes.
        Cache::put(
            'customer_registration_' . $email,
            [
                'name' => $validated['name'],
                'email' => $email,
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'otp' => Hash::make($otp),
                'verified' => false,
            ],
            now()->addMinutes(10)
        );

        // Send OTP email
        Mail::raw(
            "Your Tillora verification code is: {$otp}\n\n"
            . "This code will expire in 10 minutes.\n\n"
            . "If you did not request this code, you can safely ignore this email.",
            function ($message) use ($email) {
                $message
                    ->to($email)
                    ->subject('Tillora Email Verification Code');
            }
        );

        return response()->json([
            'message' => 'Verification code sent to your email.',
        ]);
    }

    /**
     * Verify the email verification OTP.
     */
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = strtolower(trim($validated['email']));

        $cacheKey = 'customer_registration_' . $email;

        $registration = Cache::get($cacheKey);

        // Registration session does not exist or has expired
        if (! $registration) {
            return response()->json([
                'message' => 'Verification code has expired or is invalid. Please request a new code.',
            ], 422);
        }

        // Check OTP
        if (! Hash::check($validated['otp'], $registration['otp'])) {
            return response()->json([
                'message' => 'The verification code is incorrect.',
            ], 422);
        }

        // Mark email as verified
        $registration['verified'] = true;

        // Keep the verified registration alive for another 10 minutes
        Cache::put(
            $cacheKey,
            $registration,
            now()->addMinutes(10)
        );

        return response()->json([
            'message' => 'Email verified successfully.',
        ]);
    }

    /**
     * Resend the email verification OTP.
     */
    public function resendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));

        $cacheKey = 'customer_registration_' . $email;

        $registration = Cache::get($cacheKey);

        // Registration session has expired
        if (! $registration) {
            return response()->json([
                'message' => 'Your registration session has expired. Please register again.',
            ], 422);
        }

        // Generate a new 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        // Replace the old OTP
        $registration['otp'] = Hash::make($otp);
        $registration['verified'] = false;

        // Reset the 10-minute expiration
        Cache::put(
            $cacheKey,
            $registration,
            now()->addMinutes(10)
        );

        // Send the new OTP
        Mail::raw(
            "Your new Tillora verification code is: {$otp}\n\n"
            . "This code will expire in 10 minutes.\n\n"
            . "If you did not request this code, you can safely ignore this email.",
            function ($message) use ($email) {
                $message
                    ->to($email)
                    ->subject('Tillora New Verification Code');
            }
        );

        return response()->json([
            'message' => 'A new verification code has been sent.',
        ]);
    }

    /**
     * Register a new customer after email verification.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));

        $cacheKey = 'customer_registration_' . $email;

        $registration = Cache::get($cacheKey);

        // Email must be verified first
        if (! $registration || ! ($registration['verified'] ?? false)) {
            return response()->json([
                'message' => 'Please verify your email before creating your account.',
            ], 422);
        }

        // Double-check that the email has not been registered
        if (Customer::where('email', $email)->exists()) {
            Cache::forget($cacheKey);

            return response()->json([
                'message' => 'An account with this email already exists.',
            ], 422);
        }

        // Create customer using the verified registration data
        $customer = Customer::create([
            'name' => $registration['name'],
            'email' => $registration['email'],
            'phone' => $registration['phone'],
            'password' => $registration['password'],
            'is_active' => true,
        ]);

        // Remove temporary registration data
        Cache::forget($cacheKey);

        // Create Sanctum token
        $token = $customer
            ->createToken('android-app')
            ->plainTextToken;

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

        $customer = Customer::where(
            'email',
            '=',
            $validated['email'],
            'and'
        )->first();

        if (
            ! $customer ||
            ! Hash::check(
                $validated['password'],
                $customer->password
            )
        ) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $customer->is_active) {
            return response()->json([
                'message' => 'Your account is currently inactive.',
            ], 403);
        }

        $token = $customer
            ->createToken('android-app')
            ->plainTextToken;

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
     * Update the currently authenticated customer's profile.
     *
     * Email intentionally cannot be changed here.
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $customer = $request->user();

        $customer->name = $validated['name'];
        $customer->phone = $validated['phone'] ?? null;
        $customer->address = $validated['address'] ?? null;
        $customer->city = $validated['city'] ?? null;
        $customer->country = $validated['country'] ?? null;

        $customer->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'customer' => $customer,
        ]);
    }

    /**
     * Logout the current customer.
     */
    public function logout(Request $request)
    {
        $request->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}