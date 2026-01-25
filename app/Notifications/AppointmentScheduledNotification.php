<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AppointmentScheduledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Appointment $appointment
    ) {}

    /**
     * Notification channels
     */
    public function via($notifiable): array
    {
        return [
            'database',
            'mail', // ✅ Email enabled
            // whatsapp will be added later via Twilio
        ];
    }

    /**
     * Email notification
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Appointment Has Been Scheduled')
            ->greeting('Hello ' . ($notifiable->name ?? ''))
            ->line('Your appointment has been successfully scheduled.')
            ->line('📅 Date: ' . $this->appointment->scheduled_date->format('d M Y'))
            ->line('⏰ Time: ' . $this->appointment->from_time . ' - ' . $this->appointment->to_time)
            ->action(
                'View Appointment',
                url('/client/appointments')
            )
            ->line('If you have any questions, feel free to contact us.');
    }

    /**
     * Database notification
     */
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
