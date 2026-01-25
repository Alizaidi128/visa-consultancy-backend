<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole('admin')) return true;

        if (
            $user->hasRole('client') &&
            $appointment->client?->user_id === $user->id
        ) {
            return true;
        }

        return false;
    }

    public function manage(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
