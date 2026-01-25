<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Deadline Automation
|--------------------------------------------------------------------------
*/
Schedule::command('deadlines:mark-missed')
    ->dailyAt('00:10')
    ->withoutOverlapping();

Schedule::command('deadlines:send-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Appointment WhatsApp Reminders
|--------------------------------------------------------------------------
| Runs every minute to catch exact 24h / 1h windows
*/
Schedule::command('appointments:send-reminders')
    ->everyMinute()
    ->withoutOverlapping();
