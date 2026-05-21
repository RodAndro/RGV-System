<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingApproved;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingAssigned;
use App\Notifications\BookingCompleted;
use App\Notifications\BookingRejected;
use App\Notifications\NewBookingReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with('employee');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by letter
        if ($request->filled('letter')) {
            $letter = $request->letter;
            $query->where('full_name', 'like', $letter . '%');
        }
        
        // Filter by search (name or reference number)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortable = ['full_name', 'reference_number', 'status', 'created_at'];
        $sort = in_array($request->get('sort'), $sortable) ? $request->get('sort') : 'created_at';
        $dir = $request->get('direction') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $bookings = $query->paginate($perPage)->appends($request->except('page'));

        $stats = [
            'total' => \App\Models\Booking::count(),
            'pending' => \App\Models\Booking::where('status', 'pending')->count(),
            'approved' => \App\Models\Booking::where('status', 'approved')->count(),
            'completed' => \App\Models\Booking::where('status', 'completed')->count(),
            'rejected' => \App\Models\Booking::where('status', 'rejected')->count(),
            'cancelled' => \App\Models\Booking::where('status', 'cancelled')->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'stats'));
    }

    public function show(Booking $booking)
    {
        return view('admin.bookings.show', compact('booking'));
    }

    public function approve(Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending work requests can be approved.');
        }

        $booking->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Send notification to guest via email
        Notification::route('mail', $booking->email)->notify(new BookingApproved($booking));

        // Send notification to assigned employee if any
        if ($booking->employee) {
            $booking->employee->notify(new BookingAssigned($booking));
        }

        return back()->with('success', 'Work request approved successfully.');
    }

    public function reject(Request $request, Booking $booking)
    {
        if (!in_array($booking->status, ['pending', 'approved'])) {
            return back()->with('error', 'This work request can no longer be rejected.');
        }

        $request->validate([
            'remarks' => 'required|string',
        ]);

        $booking->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'remarks' => $request->remarks,
        ]);

        // Send notification to guest via email
        Notification::route('mail', $booking->email)->notify(new BookingRejected($booking));

        return back()->with('success', 'Work request rejected successfully.');
    }

    public function complete(Booking $booking)
    {
        if ($booking->status !== 'approved') {
            return back()->with('error', 'Only approved work requests can be completed.');
        }

        $booking->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Send notification to guest via email
        Notification::route('mail', $booking->email)->notify(new BookingCompleted($booking));

        return back()->with('success', 'Work request marked as completed.');
    }

    public function cancel(Booking $booking)
    {
        if (!in_array($booking->status, ['pending', 'approved'])) {
            return back()->with('error', 'This work request can no longer be cancelled.');
        }

        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Send notification to guest via email
        Notification::route('mail', $booking->email)->notify(new BookingCancelled($booking));

        return back()->with('success', 'Work request cancelled successfully.');
    }

    public function assignEmployee(Request $request, Booking $booking)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
        ]);

        $booking->update([
            'employee_id' => $request->employee_id,
        ]);

        // Send notification to assigned employee
        $employee = User::find($request->employee_id);
        if ($employee) {
            $employee->notify(new BookingAssigned($booking));
        }

        return back()->with('success', 'Employee assigned successfully.');
    }

    public function calendar()
    {
        $bookings = Booking::whereIn('status', ['approved', 'pending'])
            ->get(['id', 'full_name', 'preferred_date', 'preferred_time', 'status']);
        
        return view('admin.bookings.calendar', compact('bookings'));
    }
}
