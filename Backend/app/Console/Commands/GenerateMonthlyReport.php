<?php

namespace App\Console\Commands;

use App\Mail\ReportGenerated;
use App\Models\Booking;
use App\Models\BorrowRequest;
use App\Models\Inventory;
use App\Models\Report;
use App\Models\ScheduledTask;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GenerateMonthlyReport extends Command
{
    protected $signature = 'report:generate-monthly';
    protected $description = 'Generate monthly operational summary for the previous month.';

    public function handle(): int
    {
        $task = ScheduledTask::start($this->signature);

        try {
            $lastMonth = now()->subMonth();
            $startOfMonth = $lastMonth->copy()->startOfMonth();
            $endOfMonth = $lastMonth->copy()->endOfMonth();

            $bookingsCreated = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $bookingsCompleted = Booking::whereBetween('completed_at', [$startOfMonth, $endOfMonth])->count();
            $bookingsPending = Booking::where('status', 'pending')->count();
            $inventoryTotal = Inventory::count();
            $inventoryLowStock = Inventory::lowStock()->count();
            $borrowRequests = BorrowRequest::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $borrowRequestsActive = BorrowRequest::where('status', 'borrowed')->count();

            $admin = User::role('admin')->first();

            if ($admin) {
                $report = Report::create([
                    'title' => 'Monthly Report - ' . $lastMonth->format('F Y'),
                    'type' => 'monthly_summary',
                    'summary' => "Bookings: {$bookingsCreated} created, {$bookingsCompleted} completed, {$bookingsPending} pending. "
                        . "Inventory: {$inventoryTotal} items, {$inventoryLowStock} low stock. "
                        . "Borrow Requests: {$borrowRequests} this month, {$borrowRequestsActive} active.",
                    'data' => [
                        'period' => $lastMonth->format('Y-m'),
                        'bookings_created' => $bookingsCreated,
                        'bookings_completed' => $bookingsCompleted,
                        'bookings_pending' => $bookingsPending,
                        'inventory_total' => $inventoryTotal,
                        'inventory_low_stock' => $inventoryLowStock,
                        'borrow_requests' => $borrowRequests,
                        'borrow_requests_active' => $borrowRequestsActive,
                    ],
                    'file_format' => 'json',
                    'generated_by' => $admin->id,
                    'report_date' => today(),
                    'start_date' => $startOfMonth,
                    'end_date' => $endOfMonth,
                ]);

                if ($email = env('REPORT_NOTIFICATION_EMAIL', env('BACKUP_NOTIFICATION_EMAIL'))) {
                    Mail::to($email)->send(new ReportGenerated($report->fresh()));
                }
            }

            $task->finish('success', "Monthly report for {$lastMonth->format('F Y')}: {$bookingsCreated} bookings.");
            $this->info('Monthly report generated.');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $task->finish('failed', null, $exception->getMessage());
            throw $exception;
        }
    }
}
