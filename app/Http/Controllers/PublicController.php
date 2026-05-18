<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\NewBookingReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicController extends Controller
{
    public function index()
    {
        return view('public.welcome');
    }

    public function bookingForm()
    {
        return view('public.booking');
    }

    public function storeBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
            'address' => 'required|string',
            'preferred_date' => 'required|date|after:today',
            'preferred_time' => 'required',
            'purpose_category' => 'required|string',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $existingActiveBooking = Booking::whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($request) {
                $query->where('email', $request->email)
                    ->orWhere('contact_number', $request->contact_number);
            })
            ->latest()
            ->first();

        if ($existingActiveBooking) {
            return redirect()->route('booking.track', $existingActiveBooking->reference_number)
                ->with('error', 'You already have an active work request. Please track your existing request using reference number ' . $existingActiveBooking->reference_number . '.');
        }

        // Check for double booking conflicts
        $hasConflict = Booking::where('preferred_date', $request->preferred_date)
            ->where('preferred_time', $request->preferred_time)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasConflict) {
            return back()->with('error', 'This time slot is already booked. Please choose another time.')->withInput();
        }

        $booking = Booking::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'address' => $request->address,
            'preferred_date' => $request->preferred_date,
            'preferred_time' => $request->preferred_time,
            'purpose_category' => $request->purpose_category,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('bookings', 'public');
            $booking->update(['attachment_path' => $path]);
        }

        // Send notification to all admins about new booking
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewBookingReceived($booking));
        }

        return redirect()->route('booking.track', $booking->reference_number)
            ->with('success', 'Booking submitted successfully! Your reference number is ' . $booking->reference_number);
    }

    public function trackBooking($reference)
    {
        $booking = Booking::where('reference_number', $reference)->firstOrFail();
        return view('public.track', compact('booking'));
    }

    public function searchBooking(Request $request)
    {
        $booking = Booking::where('reference_number', $request->reference_number)->first();
        
        if (!$booking) {
            return back()->with('error', 'Booking not found with this reference number.');
        }

        return redirect()->route('booking.track', $booking->reference_number);
    }
}
