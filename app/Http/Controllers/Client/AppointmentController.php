<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * List client appointments
     */
    public function index(Request $request)
    {
        $appointments = Appointment::query()
            ->whereHas('client', fn ($q) =>
            $q->where('user_id', $request->user()->id)
            )
            ->latest()
            ->get();

        return inertia('Client/Appointments/Index', [
            'appointments' => $appointments,
        ]);
    }

    /**
     * Show appointment (client-only, read-only)
     */
    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        return inertia('Client/Appointments/Show', [
            'appointment' => $appointment->load('client'),
        ]);
    }
}
