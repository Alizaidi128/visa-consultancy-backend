<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected Messaging $messaging;
    protected string $projectId;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;

        $this->projectId = config('services.firebase.project_id');

        if (! $this->projectId) {
            throw new \RuntimeException('Firebase project ID is not configured.');
        }
    }

    public function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data = []
    ): void {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(
                Notification::create($title, $body)
            )
            ->withData(
                collect($data)->map(fn ($v) => (string) $v)->toArray()
            );

        try {
            $this->messaging->send($message);
        } catch (\Throwable $e) {
            Log::error('FCM send failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
