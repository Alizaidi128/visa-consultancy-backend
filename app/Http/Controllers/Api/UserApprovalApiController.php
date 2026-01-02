<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use App\Notifications\UserApprovalStatusChanged;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class UserApprovalApiController extends Controller
{
    /**
     * List pending users (verified only)
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::where('status', 'pending')
            ->whereNotNull('email_verified_at')
            ->latest()
            ->get();

        return response()->json([
            'data' => $users,
        ]);
    }

    /**
     * Approve user & assign role
     */
    public function approve(Request $request, User $user)
    {
        $this->authorize('approve', $user);

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'User email is not verified.',
            ], 403);
        }

        $data = $request->validate([
            'role' => ['required', 'in:client,consultant,admin'],
        ]);

        $user->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        $user->syncRoles([$data['role']]);

        // Auto-create client profile
        if ($data['role'] === 'client') {
            Client::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $user->name,
                    'last_name'  => '',
                    'email'      => $user->email,
                    'phone'      => $user->phone,
                    'created_by' => $request->user()->id,
                ]
            );
        }

        $user->notify(
            new UserApprovalStatusChanged(
                status: 'approved',
                role: $data['role']
            )
        );
        ActivityLog::create([
            'action'        => 'user_approved',
            'loggable_type' => User::class,
            'loggable_id'   => $user->id,
            'user_id'       => $request->user()->id,
            'data'          => [
                'role' => $data['role'],
            ],
        ]);

        return response()->json([
            'message' => 'User approved successfully.',
        ]);
    }

    /**
     * Reject user
     */
    public function reject(User $user)
    {
        $this->authorize('approve', $user);

        $user->update([
            'status'      => 'rejected',
            'approved_at' => now(),
            'approved_by' => request()->user()->id,
        ]);

        $user->syncRoles([]);

        $user->notify(
            new UserApprovalStatusChanged(status: 'rejected')
        );
        ActivityLog::create([
            'action'        => 'user_rejected',
            'loggable_type' => User::class,
            'loggable_id'   => $user->id,
            'user_id'       => request()->user()->id,
            'data'          => [],
        ]);

        return response()->json([
            'message' => 'User rejected successfully.',
        ]);
    }
}
