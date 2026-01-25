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
        // 👑 Admin → everything
        if ($user->hasRole('admin')) {
            return true;
        }

        // 👤 Client → only own appointment
        if ($user->hasRole('client')) {
            return $appointment->client
                && $appointment->client->user_id === $user->id;
        }

        // 🧑‍💼 Consultant → appointment's CLIENT has any visa assigned to them
        if ($user->hasRole('consultant')) {
            return $appointment->client
                && $appointment->client
                    ->visaApplications()
                    ->where('consultant_id', $user->id)
                    ->exists();
        }

        return false;
    }

    /**
     * Create / reschedule / cancel appointment
     */
    public function manage(User $user, ?Appointment $appointment = null): bool
    {
        // 👑 Admin → always allowed
        if ($user->hasRole('admin')) {
            return true;
        }

        // 🧑‍💼 Consultant
        if ($user->hasRole('consultant')) {

            // Create (no appointment yet)
            if ($appointment === null) {
                return true;
            }

            // Only ACTIVE appointments for assigned clients
            return $appointment->isActive()
                && $appointment->client
                && $appointment->client
                    ->visaApplications()
                    ->where('consultant_id', $user->id)
                    ->exists();
        }

        return false;
    }
}
