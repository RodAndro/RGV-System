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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $dashboard = Cache::remember('admin.dashboard.index', now()->addSeconds(20), function () {
            $statsRow = DB::query()
                ->selectSub(Booking::query()->selectRaw('COUNT(*)'), 'total_bookings')
                ->selectSub(Booking::query()->where('status', 'pending')->selectRaw('COUNT(*)'), 'pending_bookings')
                ->selectSub(Booking::query()->where('status', 'approved')->selectRaw('COUNT(*)'), 'approved_bookings')
                ->selectSub(Booking::query()->where('status', 'completed')->selectRaw('COUNT(*)'), 'completed_bookings')
                ->selectSub(Booking::query()->where('status', 'rejected')->selectRaw('COUNT(*)'), 'rejected_bookings')
                ->selectSub(User::role('employee')->selectRaw('COUNT(*)'), 'total_employees')
                ->selectSub(Inventory::query()->selectRaw('COUNT(*)'), 'inventory_count')
                ->selectSub(BorrowRequest::query()->where('status', 'borrowed')->selectRaw('COUNT(*)'), 'borrowed_items')
                ->selectSub(Inventory::query()->whereColumn('quantity', '<=', 'low_stock_threshold')->selectRaw('COUNT(*)'), 'low_stock_alerts')
                ->selectSub(BorrowRequest::query()->where('status', 'returned')->selectRaw('COUNT(*)'), 'returned_items')
                ->selectSub(ImportLog::query()->whereIn('status', ['queued', 'processing'])->selectRaw('COUNT(*)'), 'running_imports')
                ->selectSub(ExportLog::query()->whereIn('status', ['queued', 'processing'])->selectRaw('COUNT(*)'), 'running_exports')
                ->selectSub(BackupMonitoring::query()->latest()->limit(1)->select('status'), 'latest_backup_status')
                ->selectSub(AuditLog::query()->whereDate('created_at', today())->selectRaw('COUNT(*)'), 'audit_events_today')
                ->selectSub(ApiRateLimit::query()->whereDate('created_at', today())->selectRaw('COUNT(*)'), 'api_requests_today')
                ->first();

            $stats = (array) $statsRow;
            foreach ($stats as $key => $value) {
                $stats[$key] = $key === 'latest_backup_status' ? ($value ?? 'unknown') : (int) $value;
            }

            $recentBookings = Booking::latest()->take(5)->get();
            $recentBorrowRequests = BorrowRequest::latest()->take(5)->get();
            $pendingBookings = Booking::pending()->take(5)->get();

            $monthExpression = DB::connection()->getDriverName() === 'pgsql'
                ? "TO_CHAR(preferred_date, 'MM')"
                : "strftime('%m', preferred_date)";

            $monthlyBookingsRaw = Booking::selectRaw("{$monthExpression} as month, COUNT(*) as count")
                ->whereYear('preferred_date', now()->year)
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            $monthlyBookings = [];
            for ($month = 1; $month <= 12; $month++) {
                $key = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
                $monthlyBookings[] = $monthlyBookingsRaw[$key] ?? 0;
            }

            return compact('stats', 'recentBookings', 'recentBorrowRequests', 'pendingBookings', 'monthlyBookings');
        });

        return view('admin.dashboard', $dashboard);
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

        $monthExpression = DB::connection()->getDriverName() === 'pgsql'
            ? "TO_CHAR(preferred_date, 'MM')"
            : "strftime('%m', preferred_date)";

        $monthlyBookingsRaw = Booking::selectRaw("{$monthExpression} as month, COUNT(*) as count")
            ->when($dateFrom, fn($q) => $q->whereDate('preferred_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('preferred_date', '<=', $dateTo))
            ->whereYear('preferred_date', now()->year)
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
