<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Services\BorrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BorrowRequestController extends Controller
{
    public function __construct(private readonly BorrowService $borrowService)
    {
    }

    public function index(Request $request)
    {
        $query = BorrowRequest::with(['employee', 'borrowItems.inventory']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('letter')) {
            $letter = $request->letter;
            $query->whereHas('employee', function($q) use ($letter) {
                $q->where('name', 'like', $letter . '%');
            });
        }

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
        try {
            $this->borrowService->approve($borrowRequest, Auth::user());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Borrow request approved successfully.');
    }

    public function reject(Request $request, BorrowRequest $borrowRequest)
    {
        $request->validate([
            'remarks' => 'required|string',
        ]);

        try {
            $this->borrowService->reject($borrowRequest, $request->remarks);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Borrow request rejected successfully.');
    }

    public function markBorrowed(BorrowRequest $borrowRequest)
    {
        try {
            $this->borrowService->markBorrowed($borrowRequest);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Borrow request marked as borrowed.');
    }

    public function returnItems(Request $request, BorrowRequest $borrowRequest)
    {
        try {
            $this->borrowService->returnItems($borrowRequest, $borrowRequest->borrowItems->map(fn ($item) => [
                'borrow_item_id' => $item->id,
                'condition_returned' => $item->condition_borrowed,
            ])->toArray());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Items returned successfully.');
    }
}
