<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Inventory;
use App\Models\BorrowRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function bookings(Request $request)
    {
        $query = Booking::with('employee');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('preferred_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('preferred_date', '<=', $request->date_to);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qry) use ($q) {
                $qry->where('reference_number', 'like', "%{$q}%")
                    ->orWhere('full_name', 'like', "%{$q}%");
            });
        }

        $stats = Cache::remember('admin.stats.bookings', now()->addSeconds(20), function () {
            $row = Booking::query()->selectRaw("COUNT(*) as total, COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending, COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved, COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed, COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected, COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled")
                ->first();

            return array_map('intval', $row->getAttributes());
        });

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $bookings = $query->latest()->paginate($perPage)->appends($request->except('page'));
        return view('admin.reports.bookings', compact('bookings', 'stats'));
    }

    public function inventory(Request $request)
    {
        $query = Inventory::with(['category']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stats = Cache::remember('admin.stats.inventory', now()->addSeconds(20), function () {
            $row = Inventory::query()->selectRaw("COUNT(*) as total, COUNT(CASE WHEN status = 'available' THEN 1 END) as available, COUNT(CASE WHEN status = 'borrowed' THEN 1 END) as borrowed, COUNT(CASE WHEN status = 'maintenance' THEN 1 END) as maintenance, COUNT(CASE WHEN status = 'damaged' THEN 1 END) as damaged")
                ->first();

            return array_map('intval', $row->getAttributes());
        });

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $inventory = $query->latest()->paginate($perPage)->appends($request->except('page'));
        return view('admin.reports.inventory', compact('inventory', 'stats'));
    }

    public function borrowRequests(Request $request)
    {
        $query = BorrowRequest::with(['employee', 'borrowItems.inventory']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stats = Cache::remember('admin.stats.borrow-requests', now()->addSeconds(20), function () {
            $row = BorrowRequest::query()->selectRaw("COUNT(*) as total, COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending, COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved, COUNT(CASE WHEN status = 'borrowed' THEN 1 END) as borrowed, COUNT(CASE WHEN status = 'returned' THEN 1 END) as returned, COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected")
                ->first();

            return array_map('intval', $row->getAttributes());
        });

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $borrowRequests = $query->latest()->paginate($perPage)->appends($request->except('page'));
        return view('admin.reports.borrow-requests', compact('borrowRequests', 'stats'));
    }

    public function users(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $stats = Cache::remember('admin.stats.users', now()->addSeconds(20), function () {
            $row = DB::query()
                ->selectSub(User::query()->selectRaw('COUNT(*)'), 'total')
                ->selectSub(User::role('admin')->selectRaw('COUNT(*)'), 'admins')
                ->selectSub(User::role('employee')->selectRaw('COUNT(*)'), 'employees')
                ->selectSub(User::query()->where('is_active', true)->selectRaw('COUNT(*)'), 'active')
                ->first();

            return array_map('intval', (array) $row);
        });

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $users = $query->latest()->paginate($perPage)->appends($request->except('page'));
        return view('admin.reports.users', compact('users', 'stats'));
    }
}
