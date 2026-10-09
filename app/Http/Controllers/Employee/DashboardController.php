<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Inventory;
use App\Models\BorrowRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $stats = Cache::remember("employee.dashboard.stats.{$user->id}", now()->addSeconds(20), function () use ($user) {
            $row = $user->borrowRequests()->selectRaw("COUNT(*) as my_borrow_requests, COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_requests, COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_requests, COUNT(CASE WHEN status = 'borrowed' THEN 1 END) as borrowed_items, COUNT(CASE WHEN status = 'returned' THEN 1 END) as returned_items")
                ->first();

            return array_map('intval', $row->getAttributes());
        });

        $availableInventory = Inventory::available()->with('category')->take(10)->get();
        $myBorrowRequests = $user->borrowRequests()->with('borrowItems.inventory')->latest()->take(5)->get();
        $lowStockItems = Inventory::lowStock()->with('category')->take(5)->get();
        $assignedBookings = Booking::where('employee_id', $user->id)->latest()->take(5)->get();

        return view('employee.dashboard', compact(
            'stats',
            'availableInventory',
            'myBorrowRequests',
            'lowStockItems',
            'assignedBookings'
        ));
    }
}
