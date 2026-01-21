<?php

namespace App\Notifications;

use App\Models\Deadline;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeadlineReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Deadline $deadline,
        public string $event // reminder | missed
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'deadline',
            'event' => $this->event, // reminder | missed
            'deadline_id' => $this->deadline->id,
            'title' => $this->deadline->title,
            'due_date' => $this->deadline->due_date->toDateString(),
            'visa_id' => $this->deadline->visa_application_id,
            'status' => $this->deadline->status,
        ];
    }
}
