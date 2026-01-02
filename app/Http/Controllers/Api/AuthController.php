<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Mobile login (Sanctum token-based)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_name' => ['required', 'string'], // important
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $user = $request->user();

        // 🔐 Block unverified email
        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            return response()->json([
                'message' => 'Email address is not verified.',
                'code' => 'EMAIL_NOT_VERIFIED',
            ], 403);
        }

        // 🔐 Block unapproved users
        if (! $user->isApproved()) {
            Auth::logout();

            return response()->json([
                'message' => 'Account is awaiting admin approval.',
                'code' => 'ACCOUNT_NOT_APPROVED',
            ], 403);
        }

        // 🔑 Create device-specific token
        $token = $user->createToken(
            $request->device_name,
            abilities: ['mobile']
        )->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Logout from current device
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        return new UserResource(
            $request->user()->load('roles')
        );
    }

}
