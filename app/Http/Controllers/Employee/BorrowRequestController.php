<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Models\Inventory;
use App\Models\User;
use App\Notifications\BorrowRequestBorrowed;
use App\Notifications\BorrowRequestReturned;
use App\Notifications\NewBorrowRequestReceived;
use Illuminate\Http\Request;

class BorrowRequestController extends Controller
{
    public function index()
    {
        $borrowRequests = auth()->user()->borrowRequests()
            ->with('borrowItems.inventory')
            ->latest()
            ->paginate(20);
        return view('employee.borrow-requests.index', compact('borrowRequests'));
    }

    public function create()
    {
        $availableInventory = Inventory::available()->with('category')->get();
        return view('employee.borrow-requests.create', compact('availableInventory'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'borrow_date' => 'required|date|after_or_equal:today',
            'due_date' => 'required|date|after:borrow_date',
            'reason' => 'required|string',
        ]);

        // Filter out empty rows (no inventory_id selected or no quantity)
        $items = collect($request->items)->filter(function ($item) {
            return !empty($item['inventory_id']) && !empty($item['quantity']);
        })->values();

        if ($items->isEmpty()) {
            return back()->with('error', 'Please select at least one item with a quantity.')->withInput();
        }

        // Validate only non-empty items
        $request->merge(['items' => $items->toArray()]);
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.inventory_id' => 'required|exists:inventories,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $requestedInventoryIds = $items->pluck('inventory_id');

        if ($requestedInventoryIds->duplicates()->isNotEmpty()) {
            return back()
                ->with('error', 'Duplicate item detected. Please add each inventory item only once per borrow request.')
                ->withInput();
        }

        $activeDuplicate = BorrowRequest::where('employee_id', auth()->id())
            ->whereIn('status', ['pending', 'approved', 'borrowed'])
            ->whereHas('borrowItems', function ($query) use ($requestedInventoryIds) {
                $query->whereIn('inventory_id', $requestedInventoryIds);
            })
            ->with('borrowItems.inventory')
            ->latest()
            ->first();

        if ($activeDuplicate) {
            $itemNames = $activeDuplicate->borrowItems
                ->whereIn('inventory_id', $requestedInventoryIds)
                ->pluck('inventory.name')
                ->filter()
                ->join(', ');

            return back()
                ->with('error', 'You already have an active borrow request for ' . ($itemNames ?: 'one or more selected items') . '. Please track request ' . $activeDuplicate->request_number . '.')
                ->withInput();
        }

        // Check if items are available in sufficient quantity
        foreach ($items as $item) {
            $inventory = Inventory::find($item['inventory_id']);
            if ($inventory->quantity < $item['quantity']) {
                return back()->with('error', 'Insufficient quantity for ' . $inventory->name)->withInput();
            }
        }

        $borrowRequest = BorrowRequest::create([
            'employee_id' => auth()->id(),
            'status' => 'pending',
            'reason' => $request->reason,
            'borrow_date' => $request->borrow_date,
            'due_date' => $request->due_date,
        ]);

        foreach ($items as $item) {
            $borrowRequest->borrowItems()->create([
                'inventory_id' => $item['inventory_id'],
                'quantity' => $item['quantity'],
                'condition_borrowed' => 'good',
            ]);
        }

        // Send notification to admins about new borrow request
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewBorrowRequestReceived($borrowRequest));
        }

        return redirect()->route('employee.borrow-requests.index')
            ->with('success', 'Borrow request submitted successfully.');
    }

    public function show(BorrowRequest $borrowRequest)
    {
        abort_if($borrowRequest->employee_id !== auth()->id(), 403);
        $borrowRequest->load(['borrowItems.inventory', 'approvedBy']);
        return view('employee.borrow-requests.show', compact('borrowRequest'));
    }

    public function returnItem(Request $request, BorrowRequest $borrowRequest)
    {
        abort_if($borrowRequest->employee_id !== auth()->id(), 403);

        $request->validate([
            'items' => 'required|array',
            'items.*.borrow_item_id' => 'required|exists:borrow_items,id',
            'items.*.condition_returned' => 'required|in:new,good,fair,poor,damaged',
            'items.*.damage_notes' => 'nullable|string',
        ]);

        foreach ($request->items as $item) {
            $borrowItem = $borrowRequest->borrowItems()->find($item['borrow_item_id']);
            $borrowItem->update([
                'condition_returned' => $item['condition_returned'],
                'damage_notes' => $item['damage_notes'] ?? null,
                'is_returned' => true,
                'returned_at' => now(),
            ]);

            // Restore inventory quantity
            $inventory = $borrowItem->inventory;
            $inventory->increment('quantity', $borrowItem->quantity);
        }

        // Check if all items are returned
        if ($borrowRequest->borrowItems()->where('is_returned', false)->count() === 0) {
            $borrowRequest->update([
                'status' => 'returned',
                'return_date' => now(),
                'returned_at' => now(),
            ]);
            
            // Send notification to employee about return completion
            $borrowRequest->employee->notify(new BorrowRequestReturned($borrowRequest));
        }

        return back()->with('success', 'Items returned successfully.');
    }

    public function markBorrowed(BorrowRequest $borrowRequest)
    {
        abort_if($borrowRequest->employee_id !== auth()->id(), 403);

        $borrowRequest->update([
            'status' => 'borrowed',
            'borrowed_at' => now(),
        ]);

        // Send notification to employee
        $borrowRequest->employee->notify(new BorrowRequestBorrowed($borrowRequest));

        return back()->with('success', 'Request marked as borrowed.');
    }

    public function destroy(BorrowRequest $borrowRequest)
    {
        abort_if($borrowRequest->employee_id !== auth()->id(), 403);

        if (!in_array($borrowRequest->status, ['returned', 'rejected'])) {
            return back()->with('error', 'Only returned or rejected requests can be deleted.');
        }

        $borrowRequest->delete();

        return redirect()->route('employee.borrow-requests.index')
            ->with('success', 'Borrow request deleted. It can be restored by an admin.');
    }
}
