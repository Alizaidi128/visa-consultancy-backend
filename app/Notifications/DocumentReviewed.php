<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentReviewed extends Notification
{
    use Queueable;

    public function __construct(
        public Document $document
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'document_type' => $this->document->type,
            'status' => $this->document->status,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Document Reviewed')
            ->line("Your document '{$this->document->type}' was {$this->document->status}");
    }
}
