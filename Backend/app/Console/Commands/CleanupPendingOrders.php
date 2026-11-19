<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\ScheduledTask;
use Illuminate\Console\Command;

class CleanupPendingOrders extends Command
{
    protected $signature = 'order:cleanup-pending';
    protected $description = 'Cancel stale pending bookings after 7 days.';

    public function handle(): int
    {
        $task = ScheduledTask::start($this->signature);

        try {
            $count = Booking::where('status', 'pending')
                ->where('created_at', '<', now()->subDays(7))
                ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

            $task->finish('success', "Cancelled {$count} stale pending bookings.");
            $this->info("Cancelled {$count} stale pending bookings.");

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $task->finish('failed', null, $exception->getMessage());
            throw $exception;
        }
    }
}
