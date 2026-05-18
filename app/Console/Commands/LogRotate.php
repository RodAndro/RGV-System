<?php

namespace App\Console\Commands;

use App\Models\ScheduledTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LogRotate extends Command
{
    protected $signature = 'log:rotate';
    protected $description = 'Remove Laravel log files older than 30 days.';

    public function handle(): int
    {
        $task = ScheduledTask::start($this->signature);

        try {
            $count = 0;

            foreach (File::glob(storage_path('logs/*.log')) ?: [] as $file) {
                if (File::lastModified($file) < now()->subDays(30)->timestamp) {
                    File::delete($file);
                    $count++;
                }
            }

            $task->finish('success', "Deleted {$count} old log files.");
            $this->info("Deleted {$count} old log files.");

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $task->finish('failed', null, $exception->getMessage());
            throw $exception;
        }
    }
}
