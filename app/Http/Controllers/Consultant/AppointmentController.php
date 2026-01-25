<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use App\Models\VisaApplication;
use App\Support\ActivityLogger;
use App\Support\WhatsAppMessages;
use App\Jobs\SendWhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    /**
     * List appointments (ONLY assigned clients)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return inertia('Consultant/Appointments/Index', [
            'appointments' => Appointment::with(['client', 'visaApplication'])
                ->whereHas('client.visaApplications', fn ($q) =>
                $q->where('consultant_id', $user->id)
                )
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Show appointment
     */
    public function show(Request $request, Appointment $appointment)
    {
        abort_unless(
            $appointment->client
                ->visaApplications()
                ->where('consultant_id', $request->user()->id)
                ->exists(),
            403
        );

        return inertia('Consultant/Appointments/Show', [
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
    public function create(Request $request)
    {
        $user = $request->user();

        return inertia('Consultant/Appointments/Create', [
            // ✅ ONLY clients assigned to this consultant
            'clients' => Client::whereHas('visaApplications', fn ($q) =>
            $q->where('consultant_id', $user->id)
            )
                ->select('id', 'first_name', 'last_name')
                ->get(),
        ]);
    }

    /**
     * Store appointment
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'client_id'           => ['required', 'exists:clients,id'],
            'visa_application_id' => ['nullable', 'exists:visa_applications,id'],
            'scheduled_date'      => ['required', 'date', 'after_or_equal:today'],
            'from_time'           => ['required', 'date_format:H:i'],
            'to_time'             => ['required', 'date_format:H:i', 'after:from_time'],
            'notes'               => ['nullable', 'string'],
        ]);

        // 🔒 Ensure consultant owns this CLIENT (not visa!)
        abort_unless(
            Client::where('id', $data['client_id'])
                ->whereHas('visaApplications', fn ($q) =>
                $q->where('consultant_id', $user->id)
                )
                ->exists(),
            403
        );

        DB::transaction(function () use ($data, $user) {

            // ⛔ Time-slot conflict
            $conflict = Appointment::query()
                ->where('scheduled_date', $data['scheduled_date'])
                ->whereIn('status', ['scheduled', 'rescheduled'])
                ->where(function ($q) use ($data) {
                    $q->whereBetween('from_time', [$data['from_time'], $data['to_time']])
                        ->orWhereBetween('to_time', [$data['from_time'], $data['to_time']])
                        ->orWhere(fn ($q) =>
                        $q->where('from_time', '<=', $data['from_time'])
                            ->where('to_time', '>=', $data['to_time'])
                        );
                })
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'from_time' => 'Selected time slot is no longer available.',
                ]);
            }

            // ✅ Create appointment
            $appointment = Appointment::create([
                ...$data,
                'created_by' => $user->id,
                'status'     => 'scheduled',
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

            foreach ($recipients as $u) {
                if (preg_match('/^[1-9][0-9]{7,14}$/', $u->phone)) {
                    SendWhatsAppMessage::dispatch($u->phone, $message);
                }
            }
        });

        return redirect()->route('consultant.appointments.index');
    }

    /**
     * Reschedule appointment
     */
    public function reschedule(Request $request, Appointment $appointment)
    {
        abort_unless(
            $appointment->client
                ->visaApplications()
                ->where('consultant_id', $request->user()->id)
                ->exists(),
            403
        );

        $data = $request->validate([
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'from_time'      => ['required', 'date_format:H:i'],
            'to_time'        => ['required', 'date_format:H:i', 'after:from_time'],
        ]);

        $appointment->update([
            ...$data,
            'status' => 'rescheduled',
        ]);

        ActivityLogger::log('appointment_rescheduled', $appointment, $data);

        return back();
    }

    /**
     * Cancel appointment
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        abort_unless(
            $appointment->client
                ->visaApplications()
                ->where('consultant_id', $request->user()->id)
                ->exists(),
            403
        );

        if ($appointment->status === 'cancelled') {
            return back();
        }

        $appointment->update(['status' => 'cancelled']);

        ActivityLogger::log('appointment_cancelled', $appointment);

        // 📧 Notify client
        $appointment->client->user?->notify(
            new \App\Notifications\AppointmentCancelledNotification($appointment)
        );

        return back();
    }

    /**
     * Calendar view
     */
    public function calendar(Request $request)
    {
        $user = $request->user();

        return inertia('Consultant/Appointments/Calendar', [
            'appointments' => Appointment::with('client')
                ->whereHas('client.visaApplications', fn ($q) =>
                $q->where('consultant_id', $user->id)
                )
                ->get()
                ->map(fn ($a) => [
                    ...$a->toArray(),
                    'scheduled_date' => $a->scheduled_date->format('Y-m-d'),
                ]),
        ]);
    }
}
