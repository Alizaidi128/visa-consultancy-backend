<?php

namespace App\Console\Commands;

use App\Notifications\DeadlineReminderNotification;
use Illuminate\Console\Command;
use App\Models\Deadline;
use App\Models\ActivityLog;
use Carbon\Carbon;

class MarkMissedDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'deadlines:mark-missed';

    /**
     * The console command description.
     */
    protected $description = 'Mark overdue deadlines as missed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();

        $deadlines = Deadline::where('status', 'pending')
            ->whereDate('due_date', '<', $today)
            ->get();

        if ($deadlines->isEmpty()) {
            $this->info('No missed deadlines found.');
            return Command::SUCCESS;
        }

        foreach ($deadlines as $deadline) {
            $deadline->update([
                'status' => 'missed',
            ]);

            ActivityLog::create([
                'loggable_type' => Deadline::class,
                'loggable_id' => $deadline->id,
                'action' => 'deadline_missed',
                'performed_by' => null,
                'data' => [
                    'due_date' => $deadline->due_date,
                ],
            ]);

            foreach ([
                         $deadline->visaApplication?->client?->user,
                         $deadline->visaApplication?->consultant
                     ] as $user) {
                if ($user) {
                    $user->notify(
                        new DeadlineReminderNotification($deadline, 'missed')
                    );
                }
            }
        }


        $this->info("Marked {$deadlines->count()} deadlines as missed.");

        return Command::SUCCESS;
    }
}
