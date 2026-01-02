<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view (INERTIA + VUE).
     */
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 🔐 Attempt authentication
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Block if email is NOT verified
        |--------------------------------------------------------------------------
        */
        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Your email address is not verified yet. Please check your inbox.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Block if admin has NOT approved the account
        |--------------------------------------------------------------------------
        */
        if (! $user->isApproved()) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Your account is awaiting admin approval.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Role-based redirect (APPROVED + VERIFIED only)
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('admin')) {
            return redirect()->intended('/dashboard');
        }

        if ($user->hasRole('consultant')) {
            return redirect()->intended('/consultant/dashboard');
        }

        if ($user->hasRole('client')) {
            return redirect()->intended('/client/dashboard');
        }

        // Fallback (should never happen)
        return redirect()->intended('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
