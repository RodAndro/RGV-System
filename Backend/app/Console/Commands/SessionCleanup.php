<?php

namespace App\Console\Commands;

use App\Models\ScheduledTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SessionCleanup extends Command
{
    protected $signature = 'session:cleanup';
    protected $description = 'Prune expired sessions.';

    public function handle(): int
    {
        $task = ScheduledTask::start($this->signature);

        try {
            Artisan::call('session:prune');
            $output = trim(Artisan::output());
            $task->finish('success', $output);
            $this->line($output);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $task->finish('failed', null, $exception->getMessage());
            throw $exception;
        }
    }
}
