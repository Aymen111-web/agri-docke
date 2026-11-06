<?php

namespace App\Http\Controllers;

use App\Exceptions\SmsDeliveryException;
use App\Http\Requests\RequestOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService,
    ) {}

    /**
     * Request an OTP code for registration.
     */
    public function requestOtp(RequestOtpRequest $request): JsonResponse
    {
        try {
            $this->otpService->generate(
                $request->validated('phone'),
                'registration'
            );

            return response()->json([
                'message' => 'Verification code sent.',
            ]);
        } catch (SmsDeliveryException $e) {
            return response()->json([
                'message' => 'Unable to send verification code right now, please try again.',
            ], 503);
        }
    }

    /**
     * Register a new user and issue a Sanctum token.
     */
    public function register(Request $request): Response
    {
        // Normalize Ethiopian phone number format
        $rawPhone = trim($request->input('phone', ''));
        $digits = preg_replace('/[^\d]/', '', $rawPhone);
        if (preg_match('/^0([79]\d{8})$/', $digits, $matches)) {
            $normalizedPhone = '+251' . $matches[1];
        } elseif (preg_match('/^251([79]\d{8})$/', $digits, $matches)) {
            $normalizedPhone = '+' . $digits;
        } elseif (preg_match('/^([79]\d{8})$/', $digits, $matches)) {
            $normalizedPhone = '+251' . $matches[1];
        } else {
            $normalizedPhone = $rawPhone;
        }
        $request->merge(['phone' => $normalizedPhone]);

        $validated = $request->validate([
            'first_name'  => 'required|string|max:255',
            'second_name' => 'required|string|max:255',
            'phone'       => 'required|string|unique:users,phone',
            'password'    => 'required|string|min:6',
            'code'        => 'nullable|string|digits:6',
        ]);

        if (! empty($validated['code'])) {
            $valid = $this->otpService->verify($validated['phone'], $validated['code'], 'registration');

            if (! $valid) {
                return response([
                    'error' => 'Invalid or expired verification code.',
                ], 422);
            }
        }

        $user = User::create([
            'first_name'        => $validated['first_name'],
            'second_name'       => $validated['second_name'],
            'phone'             => $validated['phone'],
            'password'          => Hash::make($validated['password']),
            'phone_verified_at' => now(),
            'is_admin'          => false,
            'account_status'    => 'active',
        ]);

        $requestedRole = strtolower((string) $request->input('role', 'buyer'));
        
        if ($requestedRole === 'farmer') {
            \App\Models\UserCapability::firstOrCreate(
                ['user_id' => $user->id, 'capability_type' => 'farmer'],
                ['status' => 'active', 'granted_at' => now()]
            );
        } else {
            \App\Models\UserCapability::firstOrCreate(
                ['user_id' => $user->id, 'capability_type' => 'buyer'],
                ['status' => 'active', 'granted_at' => now()]
            );
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response([
            'message' => 'Registration successful.',
            'user' => new UserResource($user->load(['capabilities', 'capabilityApplications'])),
            'token' => $token,
        ], 201);
    }

    /**
     * Authenticate a user and issue a Sanctum token.
     */
    public function login(Request $request): Response
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $rawInput = trim($validated['phone']);
        $digits = preg_replace('/[^\d]/', '', $rawInput);

        $possiblePhones = [
            $rawInput,
            $digits,
            '+' . $digits,
        ];

        if (preg_match('/^251([79]\d{8})$/', $digits, $matches)) {
            $possiblePhones[] = '+' . $digits;
            $possiblePhones[] = '0' . $matches[1];
            $possiblePhones[] = $matches[1];
        } elseif (preg_match('/^0?([79]\d{8})$/', $digits, $matches)) {
            $possiblePhones[] = '+251' . $matches[1];
            $possiblePhones[] = '251' . $matches[1];
            $possiblePhones[] = '0' . $matches[1];
            $possiblePhones[] = $matches[1];
        }

        $user = User::whereIn('phone', array_unique(array_filter($possiblePhones)))->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response([
                'error' => 'Invalid credentials.',
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response([
            'message' => 'Login successful.',
            'user' => new UserResource($user->load(['capabilities', 'capabilityApplications'])),
            'token' => $token,
        ]);
    }

    /**
     * Revoke the current access token.
     */
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response([
            'message' => 'Logged out successfully.',
        ]);
    }
}
