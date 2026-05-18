<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Inventory;
use App\Models\BorrowRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $stats = [
            'my_borrow_requests' => $user->borrowRequests()->count(),
            'pending_requests' => $user->borrowRequests()->pending()->count(),
            'approved_requests' => $user->borrowRequests()->approved()->count(),
            'borrowed_items' => $user->borrowRequests()->borrowed()->count(),
            'returned_items' => $user->borrowRequests()->where('status', 'returned')->count(),
        ];

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
