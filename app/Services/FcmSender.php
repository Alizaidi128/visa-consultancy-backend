<?php

namespace App\Services;

use App\Models\User;
use App\Models\FcmToken;
use Illuminate\Support\Facades\Log;
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
            ->filter()
            ->values()
            ->toArray();

        // 🚫 Web users / no mobile devices → skip silently
        if (empty($tokens)) {
            return;
        }

        try {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData(
                    collect($data)->mapWithKeys(fn ($v, $k) => [(string) $k => (string) $v])->toArray()
                );

            // ✅ Send to all devices
            Firebase::messaging()->sendMulticast($message, $tokens);

        } catch (\Throwable $e) {
            // 🚨 NEVER break business logic because of push
            Log::warning('FCM push failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
