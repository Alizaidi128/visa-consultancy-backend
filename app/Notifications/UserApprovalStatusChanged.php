<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserApprovalStatusChanged extends Notification
{
    use Queueable;

    /**
     * Approval status.
     * Expected values: approved | rejected
     */
    protected string $status;

    /**
     * Assigned role (optional, only for approved users)
     */
    protected ?string $role;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $status, ?string $role = null)
    {
        $this->status = $status;
        $this->role = $role;
    }

    /**
     * Only database notifications (UI driven).
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data stored in notifications table.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user_approval_status_changed',
            'status' => $this->status,
            'role' => $this->role,
            'message' => $this->status === 'approved'
                ? 'Your account has been approved'
                : 'Your account has been rejected',
        ];
    }
}
