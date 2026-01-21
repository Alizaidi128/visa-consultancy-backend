<?php

namespace App\Notifications;

use App\Models\Deadline;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class FcmNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $body,
        public array $data = []
    ) {}

    public function via($notifiable)
    {
        return ['database', FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setNotification([
                'title' => $this->title,
                'body' => $this->body,
            ])
            ->setData($this->data);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
        ];
    }
    public static function deadline(Deadline $deadline, string $event): array
    {
        return [
            'title' => $event === 'missed'
                ? 'Deadline Missed'
                : 'Upcoming Deadline',

            'body' => $event === 'missed'
                ? "You missed: {$deadline->title}"
                : "Reminder: {$deadline->title} is due on {$deadline->due_date->toDateString()}",

            'data' => [
                'type' => 'deadline',
                'deadline_id' => $deadline->id,
                'visa_id' => $deadline->visa_application_id,
            ],
        ];
    }

}
