<?php

namespace App\Notifications;

use App\Models\VisaApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisaStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public VisaApplication $visa,
        public string $newStatus
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'visa_id' => $this->visa->id,
            'status' => $this->newStatus,
            'country' => $this->visa->country,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Visa Application Status Updated')
            ->line("Your visa application status is now: {$this->newStatus}")
            ->line("Country: {$this->visa->country}");
    }
}
