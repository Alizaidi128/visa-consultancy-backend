<?php

namespace App\Http\Middleware;

use Inertia\Middleware;
use Illuminate\Http\Request;
use App\Models\User;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [

            /*
            |--------------------------------------------------------------------------
            | Auth info (already used by your app)
            |--------------------------------------------------------------------------
            */
            'auth' => [
                'user' => $user,
                'roles' => $user ? $user->getRoleNames() : [],
            ],

            /*
            |--------------------------------------------------------------------------
            | Admin-specific shared data
            |--------------------------------------------------------------------------
            | Only available if logged-in user is admin
            */
            'admin' => $user && $user->hasRole('admin')
                ? [
                    'pending_user_approvals' => User::where('status', 'pending')->count(),
                ]
                : null,

            /*
            |--------------------------------------------------------------------------
            | Notifications (already used by your app)
            |--------------------------------------------------------------------------
            */
            'notifications' => $user
                ? [
                    'unread_count' => $user->unreadNotifications()->count(),
                    'recent' => $user->notifications()
                        ->latest()
                        ->take(5)
                        ->get()
                        ->map(fn ($n) => [
                            'id' => $n->id,
                            'data' => $n->data,
                            'read_at' => $n->read_at,
                            'created_at' => $n->created_at->diffForHumans(),
                        ]),
                ]
                : null,

        ]);
    }
}
