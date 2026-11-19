<?php

namespace App\Console\Commands;

use App\Mail\ReportGenerated;
use App\Models\Booking;
use App\Models\Report;
use App\Models\ScheduledTask;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GenerateDailyReport extends Command
{
    protected $signature = 'report:generate-daily';
    protected $description = 'Generate daily operational and financial summary.';

    public function handle(): int
    {
        $task = ScheduledTask::start($this->signature);

        try {
            $completed = Booking::whereDate('completed_at', today())->count();
            $total = Booking::whereDate('created_at', today())->count();
            $admin = User::role('admin')->first();

            if ($admin) {
                $report = Report::create([
                    'title' => 'Daily Sales Report - ' . today()->toDateString(),
                    'type' => 'daily_sales',
                    'summary' => "Bookings created: {$total}; completed: {$completed}; estimated revenue/tax not available in booking schema.",
                    'data' => [
                        'bookings_created' => $total,
                        'bookings_completed' => $completed,
                        'revenue' => 0,
                        'tax' => 0,
                    ],
                    'file_format' => 'json',
                    'generated_by' => $admin->id,
                    'report_date' => today(),
                ]);

                if ($email = env('REPORT_NOTIFICATION_EMAIL', env('BACKUP_NOTIFICATION_EMAIL'))) {
                    Mail::to($email)->send(new ReportGenerated($report->fresh()));
                }
            }

            $task->finish('success', "Generated daily report for {$total} bookings.");
            $this->info('Daily report generated.');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $task->finish('failed', null, $exception->getMessage());
            throw $exception;
        }
    }
}
