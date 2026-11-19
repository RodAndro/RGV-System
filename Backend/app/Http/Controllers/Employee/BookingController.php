<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Booking;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::where('employee_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('employee.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        if ($booking->employee_id !== auth()->id()) {
            abort(403, 'This booking is not assigned to you.');
        }

        return view('employee.bookings.show', compact('booking'));
    }
}
