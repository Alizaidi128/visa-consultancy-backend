<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NewUserPendingApproval;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view (INERTIA + VUE).
     */
    public function create()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // 🔐 Validate input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 👤 Create user in PENDING state (not approved yet)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'password' => Hash::make($validated['password']),
            'status' => 'pending',
        ]);

        // 🔐 LOG USER IN
        // Required so /verify-email (auth middleware) works
        Auth::login($user);

        // 📧 Send email verification
        event(new Registered($user));

        // 🔔 Notify admins about new pending user
        User::role('admin')->each(function ($admin) use ($user) {
            $admin->notify(new NewUserPendingApproval($user));
        });

        // 🚀 Redirect to "Verify your email" screen
        return redirect()
            ->route('verification.notice')
            ->with('status', 'verification-link-sent');
    }
}
