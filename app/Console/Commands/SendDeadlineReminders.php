<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deadline;
use App\Notifications\DeadlineReminderNotification;
use Carbon\Carbon;

class SendDeadlineReminders extends Command
{
    protected $signature = 'deadlines:send-reminders';

    protected $description = 'Send upcoming deadline reminders';

    public function handle(): int
    {
        $today = Carbon::today();

        $deadlines = Deadline::where('status', 'pending')
            ->whereNotNull('reminder_offsets')
            ->get();

        foreach ($deadlines as $deadline) {
            foreach ($deadline->reminder_offsets as $daysBefore) {
                if ($deadline->due_date->equalTo(
                    $today->copy()->addDays($daysBefore)
                )) {
                    $this->notifyUsers($deadline);
                }
            }
        }

        return Command::SUCCESS;
    }

    protected function notifyUsers(Deadline $deadline): void
    {
        $visa = $deadline->visaApplication;

        $recipients = collect([
            $visa->client?->user,
            $visa->consultant,
        ])->filter();

        foreach ($recipients as $user) {
            $user->notify(
                new DeadlineReminderNotification($deadline, 'reminder')
            );
        }
    }
}
