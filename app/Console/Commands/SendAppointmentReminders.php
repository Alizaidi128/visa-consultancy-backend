<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\User;
use App\Support\WhatsAppMessages;
use App\Jobs\SendWhatsAppMessage;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Send WhatsApp reminders for upcoming appointments';

    public function handle(): void
    {
        $now = Carbon::now();

        $appointments = Appointment::where('status', 'scheduled')
            ->whereDate('scheduled_date', '>=', $now->toDateString())
            ->get();

        $recipients = User::role(['admin', 'consultant'])
            ->whereNotNull('phone')
            ->get();

        foreach ($appointments as $a) {
            $dateTime = Carbon::parse(
                $a->scheduled_date->format('Y-m-d') . ' ' . $a->from_time
            );

            if ($dateTime->diffInHours($now) === 24) {
                foreach ($recipients as $u) {
                    SendWhatsAppMessage::dispatch(
                        $u->phone,
                        WhatsAppMessages::reminder($a, '24 hours')
                    );
                }
            }

            if ($dateTime->diffInMinutes($now) === 60) {
                foreach ($recipients as $u) {
                    SendWhatsAppMessage::dispatch(
                        $u->phone,
                        WhatsAppMessages::reminder($a, '1 hour')
                    );
                }
            }
        }
    }
}
