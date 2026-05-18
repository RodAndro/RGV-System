<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfExportController extends Controller
{
    public function exportBookings(Request $request)
    {
        $bookings = Booking::with('employee')->latest()->get();
        
        $pdf = Pdf::loadView('pdf.bookings', compact('bookings'));
        return $pdf->download('bookings-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportInventory(Request $request)
    {
        $inventories = Inventory::with(['category', 'supplier'])->latest()->get();
        
        $pdf = Pdf::loadView('pdf.inventory', compact('inventories'));
        return $pdf->download('inventory-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportBooking(Booking $booking)
    {
        $booking->load('employee');
        
        $pdf = Pdf::loadView('pdf.booking-detail', compact('booking'));
        return $pdf->download('booking-' . $booking->reference_number . '.pdf');
    }
}
