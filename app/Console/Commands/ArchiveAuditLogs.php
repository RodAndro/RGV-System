<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\ScheduledTask;
use Illuminate\Console\Command;

class ArchiveAuditLogs extends Command
{
    protected $signature = 'audit:archive';
    protected $description = 'Archive audit logs older than 90 days and purge logs archived over a year ago.';

    public function handle(): int
    {
        $task = ScheduledTask::start($this->signature);

        try {
            $archived = AuditLog::where('created_at', '<', now()->subDays(90))
                ->whereNull('archived_at')
                ->update(['archived_at' => now()]);

            $purged = AuditLog::where('archived_at', '<', now()->subDays(365))->delete();

            $message = "Archived {$archived} logs, purged {$purged} permanently.";
            $task->finish('success', $message);
            $this->info($message);
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $task->finish('failed', null, $exception->getMessage());
            throw $exception;
        }
    }
}
