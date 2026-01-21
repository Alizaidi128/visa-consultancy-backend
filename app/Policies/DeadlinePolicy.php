<?php

namespace App\Policies;

use App\Models\Deadline;
use App\Models\User;

class DeadlinePolicy
{
    /**
     * View deadlines
     */
    public function view(User $user, Deadline $deadline): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (
            $user->hasRole('consultant') &&
            $deadline->visaApplication?->consultant_id === $user->id
        ) {
            return true;
        }

        if (
            $user->hasRole('client') &&
            $deadline->visaApplication?->client?->user_id === $user->id
        ) {
            return true;
        }

        return false;
    }

    /**
     * Create / update / delete deadlines
     */
    public function manage(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('consultant');
    }
}
