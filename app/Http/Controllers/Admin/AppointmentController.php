<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index()
    {
        $this->authorize('manage', Appointment::class);

        return inertia('Admin/Appointments/Index', [
            'appointments' => Appointment::with(['client', 'visaApplication'])
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Show create appointment form
     */
    public function create()
    {
        $this->authorize('manage', Appointment::class);

        return inertia('Admin/Appointments/Create', [
            'clients' => Client::select('id', 'first_name', 'last_name')->get(),
        ]);
    }

    /**
     * Store appointment
     */
    public function store(Request $request)
    {
        $this->authorize('manage', Appointment::class);

        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'visa_application_id' => ['nullable', 'exists:visa_applications,id'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'from_time' => ['required', 'date_format:H:i'],
            'to_time' => ['required', 'date_format:H:i', 'after:from_time'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $conflict = Appointment::query()
                ->where('scheduled_date', $data['scheduled_date'])
                ->whereIn('status', ['scheduled', 'rescheduled'])
                ->where(function ($q) use ($data) {
                    $q->whereBetween('from_time', [$data['from_time'], $data['to_time']])
                        ->orWhereBetween('to_time', [$data['from_time'], $data['to_time']])
                        ->orWhere(function ($q) use ($data) {
                            $q->where('from_time', '<=', $data['from_time'])
                                ->where('to_time', '>=', $data['to_time']);
                        });
                })
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                abort(422, 'Selected time slot is no longer available.');
            }

            $appointment = Appointment::create([
                ...$data,
                'created_by' => auth()->id(),
                'status' => 'scheduled',
            ]);

            ActivityLogger::log(
                'appointment_created',
                $appointment,
                $data
            );

            $appointment->client->user?->notify(
                new \App\Notifications\AppointmentScheduledNotification($appointment)
            );
        });

        return redirect()->route('admin.appointments.index');
    }
    public function calendar()
    {
        return inertia('Admin/Appointments/Calendar', [
            'appointments' => Appointment::with('client')
                ->get()
                ->map(fn ($a) => [
                    ...$a->toArray(),
                    'scheduled_date' => $a->scheduled_date->format('Y-m-d'),
                ]),
        ]);
    }


}
