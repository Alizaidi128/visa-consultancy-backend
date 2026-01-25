<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\WhatsAppMessages;
use App\Jobs\SendWhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    /**
     * Appointments list
     */
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
     * Show appointment
     */
    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        return inertia('Admin/Appointments/Show', [
            'appointment' => $appointment->load([
                'client',
                'visaApplication',
                'creator',
            ]),
        ]);
    }

    /**
     * Create form
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
            'client_id'           => ['required', 'exists:clients,id'],
            'visa_application_id' => ['nullable', 'exists:visa_applications,id'],
            'scheduled_date'      => ['required', 'date', 'after_or_equal:today'],
            'from_time'           => ['required', 'date_format:H:i'],
            'to_time'             => ['required', 'date_format:H:i', 'after:from_time'],
            'notes'               => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {

            // 🔒 Conflict check
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
                throw ValidationException::withMessages([
                    'from_time' => 'Selected time slot is no longer available.',
                ]);
            }

            // ✅ Create
            $appointment = Appointment::create([
                ...$data,
                'created_by' => auth()->id(),
                'status'     => 'scheduled',
            ]);

            ActivityLogger::log(
                'appointment_created',
                $appointment,
                $data
            );

            // 📧 Notify client
            $appointment->client->user?->notify(
                new \App\Notifications\AppointmentScheduledNotification($appointment)
            );

            // 📲 WhatsApp → Admin & Consultant
            $recipients = User::role(['admin', 'consultant'])
                ->whereNotNull('phone')
                ->get();

            $message = WhatsAppMessages::appointmentCreated($appointment);

            foreach ($recipients as $user) {
                if (preg_match('/^[1-9][0-9]{7,14}$/', $user->phone)) {
                    SendWhatsAppMessage::dispatch($user->phone, $message);
                }
            }
        });

        return redirect()->route('admin.appointments.index');
    }

    /**
     * Reschedule appointment
     */
    public function reschedule(Request $request, Appointment $appointment)
    {
        $this->authorize('manage', $appointment);

        $data = $request->validate([
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'from_time'      => ['required', 'date_format:H:i'],
            'to_time'        => ['required', 'date_format:H:i', 'after:from_time'],
        ]);

        $conflict = Appointment::query()
            ->where('id', '!=', $appointment->id)
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
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'from_time' => 'Selected time slot is no longer available.',
            ]);
        }

        $appointment->update([
            ...$data,
            'status' => 'rescheduled',
        ]);

        ActivityLogger::log(
            'appointment_rescheduled',
            $appointment,
            $data
        );

        // 📲 WhatsApp
        $recipients = User::role(['admin', 'consultant'])
            ->whereNotNull('phone')
            ->get();

        $message = WhatsAppMessages::appointmentRescheduled($appointment);

        foreach ($recipients as $user) {
            if (preg_match('/^[1-9][0-9]{7,14}$/', $user->phone)) {
                SendWhatsAppMessage::dispatch($user->phone, $message);
            }
        }

        return back();
    }

    /**
     * Cancel appointment
     */
    /**
     * Cancel appointment (Admin)
     */
    public function cancel(Appointment $appointment)
    {
        $this->authorize('manage', $appointment);

        // Prevent double cancel
        if ($appointment->status === 'cancelled') {
            return back();
        }

        $appointment->update([
            'status' => 'cancelled',
        ]);

        ActivityLogger::log(
            'appointment_cancelled',
            $appointment
        );

        // WhatsApp → Admin & Consultant
        $recipients = User::role(['admin', 'consultant'])
            ->whereNotNull('phone')
            ->get();

        $message = WhatsAppMessages::appointmentCancelled($appointment);

        foreach ($recipients as $user) {
            if (!preg_match('/^[1-9][0-9]{7,14}$/', $user->phone)) {
                continue;
            }

            SendWhatsAppMessage::dispatch($user->phone, $message);
        }

        return back();
    }


    /**
     * Calendar view
     */
    public function calendar()
    {
        $this->authorize('manage', Appointment::class);

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
