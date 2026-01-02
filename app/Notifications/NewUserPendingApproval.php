<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewUserPendingApproval extends Notification
{
    use Queueable;

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Use DATABASE only (no mail for now)
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data saved in notifications table
     * (consumed by Inertia + Vue)
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user_approval',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'email' => $this->user->email,
            'message' => "New user {$this->user->name} is awaiting approval",
            'action_url' => '/admin/user-approvals',
        ];
    }

}
