<?php

namespace App\Services;

use App\Models\User;
use App\Models\FcmToken;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FcmSender
{
    public static function send(
        User $user,
        string $title,
        string $body,
        array $data = []
    ): void {

        // ✅ Get ALL tokens for this user (multi-device safe)
        $tokens = FcmToken::where('user_id', $user->id)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        // ✅ Send to all devices
        Firebase::messaging()->sendMulticast($message, $tokens);
    }
}
