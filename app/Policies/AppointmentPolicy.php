<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * View appointment
     */
    public function view(User $user, Appointment $appointment): bool
    {
        // Admin → everything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Consultant → only appointments of assigned visas
        if ($user->hasRole('consultant')) {
            return $appointment->visaApplication
                && $appointment->visaApplication->consultant_id === $user->id;
        }

        // Client → only their own appointment
        if ($user->hasRole('client')) {
            return $appointment->client
                && $appointment->client->user_id === $user->id;
        }

        return false;
    }

    /**
     * Create / reschedule / cancel appointment
     */
    public function manage(User $user, ?Appointment $appointment = null): bool
    {
        // Admin → always
        if ($user->hasRole('admin')) {
            return true;
        }

        // Consultant → only assigned visa
        if ($user->hasRole('consultant') && $appointment) {
            return $appointment->visaApplication
                && $appointment->visaApplication->consultant_id === $user->id;
        }

        return false;
    }
}
