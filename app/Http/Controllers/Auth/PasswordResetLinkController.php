<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * SECURITY NOTE:
     * We intentionally do NOT reveal whether the email exists.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Attempt to send the reset link.
        // If the user does not exist, Laravel silently ignores it.
        Password::sendResetLink(
            $request->only('email')
        );

        // Always return the same response to prevent user enumeration
        return back()->with(
            'status',
            'If an account exists for this email, a password reset link has been sent.'
        );
    }
}
