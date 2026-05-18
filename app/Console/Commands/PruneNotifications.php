<?php

namespace App\Console\Commands;

use App\Models\ScheduledTask;
use App\Models\SystemNotification;
use Illuminate\Console\Command;

class PruneNotifications extends Command
{
    protected $signature = 'notification:prune';
    protected $description = 'Delete read notifications older than 90 days.';

    public function handle(): int
    {
        $task = ScheduledTask::start($this->signature);

        try {
            $count = SystemNotification::whereNotNull('read_at')
                ->where('read_at', '<', now()->subDays(90))
                ->delete();

            $task->finish('success', "Deleted {$count} old notifications.");
            $this->info("Deleted {$count} old notifications.");

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $task->finish('failed', null, $exception->getMessage());
            throw $exception;
        }
    }
}
