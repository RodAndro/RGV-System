<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Inventory;
use App\Models\User;
use App\Notifications\NewBookingReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class PublicController extends Controller
{
    public function index()
    {
        return view('public.welcome');
    }

    public function bookingForm(Request $request)
    {
        $trackedBooking = null;
        $trackingError = null;

        if ($request->filled('reference')) {
            $ref = trim((string) $request->query('reference'));
            $trackedBooking = Booking::where('reference_number', $ref)->first();
            if (!$trackedBooking) {
                $trackingError = 'No booking found with reference number "' . e($ref) . '". Please check your reference number and try again.';
            }
        }

        return view('public.booking', compact('trackedBooking', 'trackingError'));
    }

    public function storeBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
            'address' => 'required|string',
            'preferred_date' => 'required|date|after:today',
            'preferred_time' => [
                'required',
                function ($attribute, $value, $fail) {
                    $timestamp = strtotime((string) $value);
                    if ($timestamp === false) {
                        return $fail('The preferred time format is invalid.');
                    }
                    $hourMinute = date('H:i', $timestamp);
                    if ($hourMinute < '08:00' || $hourMinute > '17:00') {
                        return $fail('Preferred time must be within office hours (8:00 AM - 5:00 PM).');
                    }
                },
            ],
            'purpose_category' => 'required|string',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
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
        $ref = trim((string) ($request->input('reference_number') ?? $request->query('reference_number') ?? $request->input('reference') ?? $request->query('reference') ?? ''));

        if ($ref === '') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a booking reference number.',
                ], 422);
            }
            return redirect()->route('booking.form')->with('error', 'Please enter a booking reference number.');
        }

        $booking = Booking::where('reference_number', $ref)->first();

        if (!$booking) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No booking found with reference number "' . e($ref) . '". Please check your reference number and try again.',
                ], 404);
            }

            return redirect()->route('booking.form', ['reference' => $ref])
                ->with('error', 'No booking found with reference number "' . $ref . '".');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'booking' => $this->formatBookingForResponse($booking),
            ]);
        }

        return redirect()->route('booking.form', ['reference' => $booking->reference_number]);
    }

    public function formatBookingForResponse(Booking $booking): array
    {
        return [
            'id' => $booking->id,
            'reference_number' => $booking->reference_number,
            'full_name' => $booking->full_name,
            'email' => $booking->email,
            'contact_number' => $booking->contact_number,
            'purpose_category' => $booking->purpose_category,
            'purpose_category_label' => $this->formatPurposeCategory($booking->purpose_category),
            'address' => $booking->address,
            'preferred_date' => $booking->preferred_date ? $booking->preferred_date->format('Y-m-d') : null,
            'preferred_date_formatted' => $booking->preferred_date ? $booking->preferred_date->format('F d, Y') : '—',
            'preferred_time' => $booking->preferred_time_formatted ?? ($booking->preferred_time ? Carbon::parse($booking->preferred_time)->format('g:i A') : '—'),
            'reason' => $booking->reason ?: '—',
            'status' => $booking->status,
            'status_label' => ucfirst($booking->status),
            'remarks' => $booking->remarks,
            'created_at_formatted' => $booking->created_at ? $booking->created_at->format('F d, Y - g:i A') : '—',
            'approved_at_formatted' => $booking->approved_at ? $booking->approved_at->format('F d, Y - g:i A') : null,
            'rejected_at_formatted' => $booking->rejected_at ? $booking->rejected_at->format('F d, Y - g:i A') : null,
            'completed_at_formatted' => $booking->completed_at ? $booking->completed_at->format('F d, Y - g:i A') : null,
            'cancelled_at_formatted' => $booking->cancelled_at ? $booking->cancelled_at->format('F d, Y - g:i A') : null,
        ];
    }

    protected function formatPurposeCategory(?string $category): string
    {
        return match ($category) {
            'equipment-repair' => 'Equipment Repair',
            'tool-rental' => 'Tool Rental',
            'consultation' => 'Technical Consultation',
            'maintenance' => 'Maintenance Service',
            'other' => 'Other',
            null, '' => '—',
            default => ucfirst(str_replace(['-', '_'], ' ', $category)),
        };
    }

    /**
     * Resolve an inventory item exclusively from its immutable QR token.
     * The token is the only input used for lookup — never an item ID.
     */
    public function qrLookup(string $token)
    {
        abort_unless(preg_match('/\AQR-[A-Z0-9]{32}\z/', $token) === 1, 404);

        $inventory = Inventory::where('qr_token', $token)->firstOrFail();

        return view('public.inventory-qr', compact('inventory'));
    }
}
