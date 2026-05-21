<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Notifications\BorrowRequestApproved;
use App\Notifications\BorrowRequestRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BorrowRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = BorrowRequest::with(['employee', 'borrowItems.inventory']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by letter
        if ($request->filled('letter')) {
            $letter = $request->letter;
            $query->whereHas('employee', function($q) use ($letter) {
                $q->where('name', 'like', $letter . '%');
            });
        }
        
        // Filter by search (request number or employee name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $borrowRequests = $query->latest()->paginate($perPage)->appends($request->except('page'));
        
        $stats = [
            'total' => \App\Models\BorrowRequest::count(),
            'pending' => \App\Models\BorrowRequest::where('status', 'pending')->count(),
            'approved' => \App\Models\BorrowRequest::where('status', 'approved')->count(),
            'borrowed' => \App\Models\BorrowRequest::where('status', 'borrowed')->count(),
            'returned' => \App\Models\BorrowRequest::where('status', 'returned')->count(),
            'rejected' => \App\Models\BorrowRequest::where('status', 'rejected')->count(),
        ];
        
        return view('admin.borrow-requests.index', compact('borrowRequests', 'stats'));
    }

    public function show(BorrowRequest $borrowRequest)
    {
        $borrowRequest->load(['employee', 'borrowItems.inventory']);
        return view('admin.borrow-requests.show', compact('borrowRequest'));
    }

    public function approve(BorrowRequest $borrowRequest)
    {
        if ($borrowRequest->status !== 'pending') {
            return back()->with('error', 'Only pending borrow requests can be approved.');
        }

        $borrowRequest->load('borrowItems.inventory');

        foreach ($borrowRequest->borrowItems as $borrowItem) {
            if (!$borrowItem->inventory || $borrowItem->inventory->quantity < $borrowItem->quantity) {
                return back()->with('error', 'Insufficient stock for ' . ($borrowItem->inventory?->name ?? 'one requested item') . '.');
            }
        }

        DB::transaction(function () use ($borrowRequest) {
            foreach ($borrowRequest->borrowItems as $borrowItem) {
                $borrowItem->inventory->decrement('quantity', $borrowItem->quantity);
            }

            $borrowRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });

        // Send notification to employee
        $borrowRequest->employee->notify(new BorrowRequestApproved($borrowRequest));

        return back()->with('success', 'Borrow request approved successfully.');
    }

    public function reject(Request $request, BorrowRequest $borrowRequest)
    {
        if (!in_array($borrowRequest->status, ['pending', 'approved'])) {
            return back()->with('error', 'This borrow request can no longer be rejected.');
        }

        $request->validate([
            'remarks' => 'required|string',
        ]);

        $borrowRequest->load('borrowItems.inventory');

        DB::transaction(function () use ($borrowRequest, $request) {
            if ($borrowRequest->status === 'approved') {
                foreach ($borrowRequest->borrowItems as $borrowItem) {
                    $borrowItem->inventory?->increment('quantity', $borrowItem->quantity);
                }
            }

            $borrowRequest->update([
                'status' => 'rejected',
                'admin_remarks' => $request->remarks,
                'rejected_at' => now(),
            ]);
        });

        // Send notification to employee
        $borrowRequest->employee->notify(new BorrowRequestRejected($borrowRequest));

        return back()->with('success', 'Borrow request rejected successfully.');
    }

    public function markBorrowed(BorrowRequest $borrowRequest)
    {
        if ($borrowRequest->status !== 'approved') {
            return back()->with('error', 'Only approved borrow requests can be marked as borrowed.');
        }

        $borrowRequest->update([
            'status' => 'borrowed',
            'borrowed_at' => now(),
        ]);

        $borrowRequest->employee->notify(new \App\Notifications\BorrowRequestBorrowed($borrowRequest));

        return back()->with('success', 'Borrow request marked as borrowed.');
    }

    public function returnItems(Request $request, BorrowRequest $borrowRequest)
    {
        if (!in_array($borrowRequest->status, ['borrowed'])) {
            return back()->with('error', 'Only currently borrowed requests can be returned.');
        }

        $borrowRequest->load('borrowItems.inventory');

        DB::transaction(function () use ($borrowRequest) {
            foreach ($borrowRequest->borrowItems as $borrowItem) {
                $borrowItem->update([
                    'is_returned' => true,
                    'returned_at' => now(),
                ]);
                $borrowItem->inventory?->increment('quantity', $borrowItem->quantity);
            }

            $borrowRequest->update([
                'status' => 'returned',
                'return_date' => now(),
                'returned_at' => now(),
            ]);
        });

        $borrowRequest->employee->notify(new \App\Notifications\BorrowRequestReturned($borrowRequest));

        return back()->with('success', 'Items returned successfully.');
    }
}
