<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordChangedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your password has been changed')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('This is a confirmation that your account password was changed successfully.')
            ->line('If you did not perform this action, please contact our support team immediately.')
            ->line('For your security, we recommend reviewing your recent account activity.')
            ->salutation('Regards, ' . config('app.name'));
    }
}
