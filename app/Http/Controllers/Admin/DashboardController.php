<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Inventory;
use App\Models\BorrowRequest;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\BackupMonitoring;
use App\Models\ExportLog;
use App\Models\ImportLog;
use App\Models\ApiRateLimit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'approved_bookings' => Booking::where('status', 'approved')->count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
            'rejected_bookings' => Booking::where('status', 'rejected')->count(),
            'total_employees' => User::role('employee')->count(),
            'inventory_count' => Inventory::count(),
            'borrowed_items' => BorrowRequest::where('status', 'borrowed')->count(),
            'low_stock_alerts' => Inventory::lowStock()->count(),
            'returned_items' => BorrowRequest::where('status', 'returned')->count(),
            'running_imports' => ImportLog::whereIn('status', ['queued', 'processing'])->count(),
            'running_exports' => ExportLog::whereIn('status', ['queued', 'processing'])->count(),
            'latest_backup_status' => BackupMonitoring::latest()->value('status') ?? 'unknown',
            'audit_events_today' => AuditLog::whereDate('created_at', today())->count(),
            'api_requests_today' => ApiRateLimit::whereDate('created_at', today())->count(),
        ];

        $recentBookings = Booking::latest()->take(5)->get();
        $recentBorrowRequests = BorrowRequest::latest()->take(5)->get();
        $pendingBookings = Booking::pending()->take(5)->get();

        // Monthly booking data for charts
        $monthlyBookingsRaw = Booking::selectRaw("strftime('%m', preferred_date) as month, COUNT(*) as count")
            ->whereRaw("strftime('%Y', preferred_date) = ?", [now()->year])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $monthlyBookings = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            $monthlyBookings[] = $monthlyBookingsRaw[$key] ?? 0;
        }

        return view('admin.dashboard', compact(
            'stats',
            'recentBookings',
            'recentBorrowRequests',
            'pendingBookings',
            'monthlyBookings'
        ));
    }

    public function stats(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $bookingQuery = Booking::query();
        $auditQuery = AuditLog::query();
        $apiQuery = ApiRateLimit::query();

        if ($dateFrom) {
            $bookingQuery->whereDate('created_at', '>=', $dateFrom);
            $auditQuery->whereDate('created_at', '>=', $dateFrom);
            $apiQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $bookingQuery->whereDate('created_at', '<=', $dateTo);
            $auditQuery->whereDate('created_at', '<=', $dateTo);
            $apiQuery->whereDate('created_at', '<=', $dateTo);
        }

        $monthlyBookingsRaw = Booking::selectRaw("strftime('%m', preferred_date) as month, COUNT(*) as count")
            ->when($dateFrom, fn($q) => $q->whereDate('preferred_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('preferred_date', '<=', $dateTo))
            ->whereRaw("strftime('%Y', preferred_date) = ?", [now()->year])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $monthlyBookings = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            $monthlyBookings[] = $monthlyBookingsRaw[$key] ?? 0;
        }

        return response()->json([
            'total_bookings' => $bookingQuery->count(),
            'pending_bookings' => (clone $bookingQuery)->where('status', 'pending')->count(),
            'approved_bookings' => (clone $bookingQuery)->where('status', 'approved')->count(),
            'completed_bookings' => (clone $bookingQuery)->where('status', 'completed')->count(),
            'rejected_bookings' => (clone $bookingQuery)->where('status', 'rejected')->count(),
            'total_employees' => User::role('employee')->count(),
            'inventory_count' => Inventory::count(),
            'borrowed_items' => BorrowRequest::where('status', 'borrowed')->count(),
            'low_stock_alerts' => Inventory::lowStock()->count(),
            'returned_items' => BorrowRequest::where('status', 'returned')->count(),
            'running_imports' => ImportLog::whereIn('status', ['queued', 'processing'])->count(),
            'running_exports' => ExportLog::whereIn('status', ['queued', 'processing'])->count(),
            'latest_backup_status' => BackupMonitoring::latest()->value('status') ?? 'unknown',
            'audit_events_today' => $auditQuery->count(),
            'api_requests_today' => $apiQuery->count(),
            'monthly_bookings' => $monthlyBookings,
            'system' => \App\Services\SystemMetricsService::collect(),
        ]);
    }
}
