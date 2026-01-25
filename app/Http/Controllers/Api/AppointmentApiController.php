<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\WhatsAppMessages;
use App\Jobs\SendWhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentApiController extends Controller
{
    /**
     * List appointments (role-aware, hide completed/cancelled)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Appointment::with(['client', 'visaApplication'])
            ->visible(); // ✅ scheduled + rescheduled only

        // 👤 Client → only own
        if ($user->hasRole('client')) {
            $query->whereHas('client', fn ($q) =>
            $q->where('user_id', $user->id)
            );
        }

        // 🧑‍💼 Consultant → only assigned visas
        if ($user->hasRole('consultant')) {
            $query->whereHas('visaApplication', fn ($q) =>
            $q->where('consultant_id', $user->id)
            );
        }

        return response()->json(
            $query->latest()->get()
        );
    }

    /**
     * Show appointment
     */
    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        // ❌ Block viewing cancelled/completed from mobile
        if (! $appointment->isActive()) {
            abort(404);
        }

        return response()->json(
            $appointment->load(['client', 'visaApplication', 'creator'])
        );
    }

    /**
     * Create appointment (Admin / Consultant)
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

        $appointment = DB::transaction(function () use ($data) {

            // 🔒 Conflict check (ACTIVE appointments only)
            $conflict = Appointment::active()
                ->where('scheduled_date', $data['scheduled_date'])
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

            $appointment = Appointment::create([
                ...$data,
                'created_by' => auth()->id(),
                'status'     => Appointment::STATUS_SCHEDULED,
            ]);

            ActivityLogger::log('appointment_created', $appointment, $data);

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

            return $appointment;
        });

        return response()->json($appointment, 201);
    }

    /**
     * Reschedule appointment
     */
    public function reschedule(Request $request, Appointment $appointment)
    {
        $this->authorize('manage', $appointment);

        // ❌ Cannot reschedule cancelled / completed
        if (! $appointment->isActive()) {
            abort(422, 'This appointment can no longer be rescheduled.');
        }

        $data = $request->validate([
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'from_time'      => ['required', 'date_format:H:i'],
            'to_time'        => ['required', 'date_format:H:i', 'after:from_time'],
        ]);

        $conflict = Appointment::active()
            ->where('id', '!=', $appointment->id)
            ->where('scheduled_date', $data['scheduled_date'])
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
            'status' => Appointment::STATUS_RESCHEDULED,
        ]);

        ActivityLogger::log('appointment_rescheduled', $appointment, $data);

        return response()->json($appointment);
    }

    /**
     * Cancel appointment
     */
    public function cancel(Appointment $appointment)
    {
        $this->authorize('manage', $appointment);

        // ❌ Already cancelled / completed
        if (! $appointment->isActive()) {
            return response()->json($appointment);
        }

        $appointment->update([
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        ActivityLogger::log('appointment_cancelled', $appointment);

        // 📧 Notify client
        $appointment->client->user?->notify(
            new \App\Notifications\AppointmentCancelledNotification($appointment)
        );

        // 📲 WhatsApp → Admin & Consultant
        $recipients = User::role(['admin', 'consultant'])
            ->whereNotNull('phone')
            ->get();

        $message = WhatsAppMessages::appointmentCancelled($appointment);

        foreach ($recipients as $user) {
            if (preg_match('/^[1-9][0-9]{7,14}$/', $user->phone)) {
                SendWhatsAppMessage::dispatch($user->phone, $message);
            }
        }

        return response()->json($appointment);
    }
}
