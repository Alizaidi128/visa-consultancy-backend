<?php

namespace App\Http\Controllers\Admin;
use App\Services\FcmSender;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use App\Notifications\UserApprovalStatusChanged;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserApprovalController extends Controller
{
    /**
     * Show all pending users.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->where('status', 'pending')
            ->whereNotNull('email_verified_at') // ✅ ONLY verified users
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/UserApprovals/Index', [
            'users' => $users,
            'highlightUser' => $request->query('highlight_user'),
        ]);
    }






    /**
     * Approve a user and assign a role.
     */
    public function approve(Request $request, User $user)
    {
        if (! $user->hasVerifiedEmail()) {
            abort(403, 'User email is not verified.');
        }

        $validated = $request->validate([
            'role' => ['required', 'in:client,consultant,admin'],
        ]);

        // Update approval status
        $user->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        // Assign role
        $user->syncRoles([$validated['role']]);

        /*
        |--------------------------------------------------------------------------
        | AUTO-CREATE CLIENT RECORD (ONLY FOR CLIENT ROLE)
        |--------------------------------------------------------------------------
        */
        if ($validated['role'] === 'client') {
            Client::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $user->name,
                    'last_name'  => '',
                    'email'      => $user->email,
                    'phone'      => $user->phone,
                    'created_by' => auth()->id(),
                ]
            );
        }

        // Notify the user
        $user->notify(
            new UserApprovalStatusChanged(
                status: 'approved',
                role: $validated['role']
            )
        );

        FcmSender::send(
            user: $user,
            title: 'Account Approved',
            body: 'Your account has been approved. You can now access the system.',
            data: [
                'screen' => 'notifications'
            ]
        );


        return back()->with('success', 'User approved and role assigned.');
    }

    /**
     * Reject a user.
     */
    public function reject(User $user)
    {
        $user->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        $user->syncRoles([]);

        $user->notify(
            new UserApprovalStatusChanged(status: 'rejected')
        );

        FcmSender::send(
            user: $user,
            title: 'Account Rejected',
            body: 'Your account request has been rejected.',
            data: [
                'screen' => 'notifications'
            ]
        );


        return back()->with('success', 'User rejected successfully.');
    }
}
