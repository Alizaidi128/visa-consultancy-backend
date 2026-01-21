<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SystemNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $title,
        public string $message,
        public array $data = []
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
        ]);
    }
    public static function fromDeadline(array $data): array
    {
        return [
            'title' => $data['event'] === 'missed'
                ? 'Deadline Missed'
                : 'Upcoming Deadline',

            'message' => $data['event'] === 'missed'
                ? "You missed the deadline: {$data['title']}"
                : "Upcoming deadline: {$data['title']} (Due {$data['due_date']})",

            'action_url' => "/visas/{$data['visa_id']}",
            'type' => 'deadline',
        ];
    }

}
