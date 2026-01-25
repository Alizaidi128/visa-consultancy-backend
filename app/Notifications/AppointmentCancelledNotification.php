<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Appointment Cancelled')
            ->line('Your appointment has been cancelled.')
            ->line('Date: ' . $this->appointment->scheduled_date->format('d/m/Y'))
            ->line(
                'Time: ' .
                substr($this->appointment->from_time, 0, 5) .
                ' - ' .
                substr($this->appointment->to_time, 0, 5)
            )
            ->line('If you have questions, please contact support.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Appointment Cancelled',
            'message' => sprintf(
                'Your appointment on %s has been cancelled.',
                $this->appointment->scheduled_date->format('d/m/Y')
            ),

            'type' => 'appointment',
            'event' => 'cancelled',
            'appointment_id' => $this->appointment->id,
            'scheduled_date' => $this->appointment->scheduled_date->format('Y-m-d'),
            'from_time' => $this->appointment->from_time,
            'to_time' => $this->appointment->to_time,
        ];
    }

}
