<?php

namespace App\Support;

use App\Models\Appointment;

class WhatsAppMessages
{
    public static function appointmentCreated(Appointment $a): string
    {
        return "📅 *New Appointment Scheduled*

Client: {$a->client->first_name} {$a->client->last_name}
Date: {$a->scheduled_date->format('d M Y')}
Time: {$a->from_time} – {$a->to_time}

— Visa Simplify";
    }

    public static function appointmentRescheduled(Appointment $a): string
    {
        return "🔄 *Appointment Rescheduled*

Client: {$a->client->first_name} {$a->client->last_name}
New Date: {$a->scheduled_date->format('d M Y')}
Time: {$a->from_time} – {$a->to_time}

— Visa Simplify";
    }

    public static function appointmentCancelled(Appointment $a): string
    {
        return "❌ *Appointment Cancelled*

Client: {$a->client->first_name} {$a->client->last_name}
Date: {$a->scheduled_date->format('d M Y')}

— Visa Simplify";
    }

    public static function reminder(Appointment $a, string $when): string
    {
        return "⏰ *Appointment Reminder ({$when})*

Client: {$a->client->first_name} {$a->client->last_name}
Date: {$a->scheduled_date->format('d M Y')}
Time: {$a->from_time} – {$a->to_time}

— Visa Simplify";
    }
}
