<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * View pending users (admin only)
     */
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasRole('admin');
    }

    /**
     * Approve / reject users (admin only)
     */
    public function approve(User $authUser, User $targetUser): bool
    {
        return $authUser->hasRole('admin');
    }
}
