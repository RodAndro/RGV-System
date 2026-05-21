<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Inventory;
use App\Models\BorrowRequest;
use App\Models\User;
use Illuminate\Http\Request;

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

        $stats = [
            'total' => \App\Models\Booking::count(),
            'pending' => \App\Models\Booking::where('status', 'pending')->count(),
            'approved' => \App\Models\Booking::where('status', 'approved')->count(),
            'completed' => \App\Models\Booking::where('status', 'completed')->count(),
            'rejected' => \App\Models\Booking::where('status', 'rejected')->count(),
            'cancelled' => \App\Models\Booking::where('status', 'cancelled')->count(),
        ];

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $bookings = $query->latest()->paginate($perPage)->appends($request->except('page'));
        return view('admin.reports.bookings', compact('bookings', 'stats'));
    }

    public function inventory(Request $request)
    {
        $query = Inventory::with(['category', 'supplier']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stats = [
            'total' => Inventory::count(),
            'available' => Inventory::where('status', 'available')->count(),
            'borrowed' => Inventory::where('status', 'borrowed')->count(),
            'maintenance' => Inventory::where('status', 'maintenance')->count(),
            'damaged' => Inventory::where('status', 'damaged')->count(),
        ];

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

        $stats = [
            'total' => BorrowRequest::count(),
            'pending' => BorrowRequest::where('status', 'pending')->count(),
            'approved' => BorrowRequest::where('status', 'approved')->count(),
            'borrowed' => BorrowRequest::where('status', 'borrowed')->count(),
            'returned' => BorrowRequest::where('status', 'returned')->count(),
            'rejected' => BorrowRequest::where('status', 'rejected')->count(),
        ];

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

        $stats = [
            'total' => User::count(),
            'admins' => User::role('admin')->count(),
            'employees' => User::role('employee')->count(),
            'active' => User::where('is_active', true)->count(),
        ];

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $users = $query->latest()->paginate($perPage)->appends($request->except('page'));
        return view('admin.reports.users', compact('users', 'stats'));
    }
}
