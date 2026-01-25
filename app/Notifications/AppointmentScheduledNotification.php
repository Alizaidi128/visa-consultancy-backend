<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentScheduledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
        // whatsapp will be added later via Twilio
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'appointment',
            'event' => 'scheduled',
            'appointment_id' => $this->appointment->id,
            'date' => $this->appointment->scheduled_date->toDateString(),
            'from' => $this->appointment->from_time,
            'to' => $this->appointment->to_time,
            'client_id' => $this->appointment->client_id,
            'action_url' => '/client/appointments',
        ];
    }
}
