<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic backups and maintenance tasks.
Schedule::command('backup:run --disable-notifications')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onSuccess(fn () => \App\Models\ScheduledTask::create([
        'command' => 'backup:run',
        'status' => 'success',
        'started_at' => now(),
        'finished_at' => now(),
    ]))
    ->onFailure(fn () => \App\Models\ScheduledTask::create([
        'command' => 'backup:run',
        'status' => 'failed',
        'started_at' => now(),
        'finished_at' => now(),
    ]));

Schedule::command('backup:run --only-db --disable-notifications')->weeklyOn(0, '03:00')->withoutOverlapping();
Schedule::command('backup:clean')->dailyAt('03:30')->withoutOverlapping();
Schedule::command('order:cleanup-pending')->hourly()->withoutOverlapping();
Schedule::command('session:cleanup')->daily()->withoutOverlapping();
Schedule::command('log:rotate')->weekly()->withoutOverlapping();
Schedule::command('report:generate-daily')->dailyAt('23:55')->withoutOverlapping();
Schedule::command('report:generate-monthly')->monthlyOn(1, '00:30')->withoutOverlapping();
Schedule::command('notification:prune')->weekly()->withoutOverlapping();
Schedule::command('audit:archive')->monthly()->withoutOverlapping();
Schedule::command('books:refresh-bestseller-stats')->hourly()->withoutOverlapping();
Schedule::command('books:warm-cache')->hourly()->withoutOverlapping();
