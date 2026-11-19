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
        $request->user()->unreadNotifications()
            ->where('type', 'borrow_request')
            ->update(['is_read' => true, 'read_at' => now()]);

        $query = BorrowRequest::with(['employee', 'borrowItems.inventory']);

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $request->status);
            }
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

        $today = now()->toDateString();
        $row = BorrowRequest::query()->selectRaw("
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
            COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
            COUNT(CASE WHEN status = 'borrowed' AND (due_date >= '{$today}' OR due_date IS NULL) THEN 1 END) as borrowed,
            COUNT(CASE WHEN status = 'returned' THEN 1 END) as returned,
            COUNT(CASE WHEN status = 'overdue' OR (status = 'borrowed' AND due_date < '{$today}') THEN 1 END) as overdue,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
            COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected
        ")->first();
        $stats = array_map('intval', $row->getAttributes());

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
            $this->borrowService->markBorrowed($borrowRequest, Auth::user());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Borrow request marked as borrowed.');
    }

    public function returnItems(Request $request, BorrowRequest $borrowRequest)
    {
        $request->validate([
            'return_location' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->borrowService->returnItems($borrowRequest, $borrowRequest->borrowItems->map(fn ($item) => [
                'borrow_item_id' => $item->id,
                'condition_returned' => $item->condition_borrowed,
            ])->toArray(), null, $request->return_location, Auth::user());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Items returned successfully.');
    }

    public function destroy(BorrowRequest $borrowRequest)
    {
        if ($borrowRequest->status !== 'returned') {
            return back()->with('error', 'Only returned borrow requests can be moved to trash.');
        }

        $borrowRequest->delete();

        return redirect()->route('admin.borrow-requests.index')
            ->with('success', 'Returned borrow request moved to trash.');
    }
}
