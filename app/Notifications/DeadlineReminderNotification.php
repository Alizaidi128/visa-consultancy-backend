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
        $isMissed = $this->event === 'missed';

        return [
            'title' => $isMissed
                ? 'Deadline Missed'
                : 'Upcoming Deadline',

            'message' => $isMissed
                ? "You missed the deadline: {$this->deadline->title}."
                : "Reminder: {$this->deadline->title} is due on {$this->deadline->due_date->format('d/m/Y')}.",

            'type' => 'deadline',
            'event' => $this->event,
            'deadline_id' => $this->deadline->id,
            'due_date' => $this->deadline->due_date->toDateString(),
            'visa_id' => $this->deadline->visa_application_id,
            'status' => $this->deadline->status,
        ];
    }

}
