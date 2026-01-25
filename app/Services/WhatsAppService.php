<?php

namespace App\Services;

use Twilio\Rest\Client;

class WhatsAppService
{
    protected Client $client;
    protected string $from;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        // Sandbox number
        $this->from = 'whatsapp:' . config('services.twilio.whatsapp_from');
    }

    public function send(string $phone, string $message): void
    {
        if (!$phone) {
            return;
        }

        $this->client->messages->create(
            'whatsapp:' . $phone,
            [
                'from' => $this->from,
                'body' => $message,
            ]
        );
    }
}
