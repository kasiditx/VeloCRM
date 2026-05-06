<?php

use App\Console\Commands\GenerateRecurringInvoices;
use App\Console\Commands\SendOverdueReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SendOverdueReminders::class)->dailyAt('09:00');
Schedule::command(GenerateRecurringInvoices::class)->dailyAt('08:00');
