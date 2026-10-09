<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Services\BorrowService;
use Illuminate\Http\Request;

class BorrowRequestController extends Controller
{
    public function __construct(private readonly BorrowService $borrowService) {}

    public function index(Request $request)
    {
        $requests = $request->user()->borrowRequests()
            ->with(['borrowItems.inventory', 'returnEvidences'])
            ->latest()
            ->get();

        return response()->json([
            'requests' => $requests->map(fn ($borrowRequest) => $this->requestPayload($borrowRequest)),
        ]);
    }

    public function show(Request $request, BorrowRequest $borrowRequest)
    {
        $this->authorizeOwnership($request, $borrowRequest);

        $borrowRequest->load(['borrowItems.inventory', 'returnEvidences']);

        return response()->json(['request' => $this->requestPayload($borrowRequest)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'borrow_date' => ['required', 'date', 'after_or_equal:today'],
            'due_date' => ['required', 'date', 'after:borrow_date'],
            'reason' => ['required', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $borrowRequest = $this->borrowService->createPending(
                $request->user(),
                $data['reason'],
                $data['borrow_date'],
                $data['due_date'],
                $data['items'],
            );
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Borrow request submitted.',
            'request' => $this->requestPayload($borrowRequest->load('borrowItems.inventory')),
        ], 201);
    }

    public function cancel(Request $request, BorrowRequest $borrowRequest)
    {
        $this->authorizeOwnership($request, $borrowRequest);

        try {
            $this->borrowService->cancel($borrowRequest, $request->user());
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Borrow request cancelled.',
            'request' => $this->requestPayload($borrowRequest->fresh('borrowItems.inventory')),
        ]);
    }

    public function returnableItems(Request $request)
    {
        $items = $request->user()->borrowRequests()
            ->where('status', 'borrowed')
                ->with(['borrowItems' => fn ($q) => $q->where('is_returned', false)->with('inventory:id,item_code,name,brand,unit,condition,location')])
            ->get()
            ->flatMap(fn ($borrowRequest) => $borrowRequest->borrowItems->map(fn ($item) => [
                'borrow_item_id' => $item->id,
                'request_number' => $borrowRequest->request_number,
                'request_id' => $borrowRequest->id,
                'borrow_date' => optional($borrowRequest->borrow_date)->format('Y-m-d'),
                'due_date' => optional($borrowRequest->due_date)->format('Y-m-d'),
                'quantity' => (int) $item->quantity,
                'condition_borrowed' => $item->condition_borrowed,
                'inventory' => [
                    'id' => $item->inventory?->id,
                    'item_code' => $item->inventory?->item_code,
                    'name' => $item->inventory?->name,
                    'brand' => $item->inventory?->brand,
                    'unit' => $item->inventory?->unit,
                    'location' => $item->inventory?->location,
                ],
            ]))
            ->values();

        return response()->json(['items' => $items]);
    }

    public function return(Request $request, BorrowRequest $borrowRequest)
    {
        $this->authorizeOwnership($request, $borrowRequest);

        $data = $request->validate([
            'idempotency_key' => ['required', 'string', 'max:64'],
            'return_location' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.borrow_item_id' => ['required', 'integer', 'distinct'],
            'items.*.condition_returned' => ['required', 'string', 'in:new,good,fair,poor,damaged'],
            'items.*.damage_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->borrowService->returnItems($borrowRequest, $data['items'], null, $data['return_location']);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        $borrowRequest->refresh()->load('borrowItems.inventory');

        return response()->json([
            'message' => 'Items returned successfully.',
            'request' => $this->requestPayload($borrowRequest),
        ]);
    }

    private function authorizeOwnership(Request $request, BorrowRequest $borrowRequest): void
    {
        if ($borrowRequest->employee_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function requestPayload(BorrowRequest $borrowRequest): array
    {
        return [
            'id' => $borrowRequest->id,
            'request_number' => $borrowRequest->request_number,
            'status' => $borrowRequest->status,
            'reason' => $borrowRequest->reason,
            'borrow_date' => optional($borrowRequest->borrow_date)->format('Y-m-d'),
            'due_date' => optional($borrowRequest->due_date)->format('Y-m-d'),
            'return_date' => optional($borrowRequest->return_date)->format('Y-m-d'),
            'return_location' => $borrowRequest->return_location,
            'admin_remarks' => $borrowRequest->admin_remarks,
            'approved_at' => optional($borrowRequest->approved_at)->toIso8601String(),
            'rejected_at' => optional($borrowRequest->rejected_at)->toIso8601String(),
            'cancelled_at' => optional($borrowRequest->cancelled_at)->toIso8601String(),
            'borrowed_at' => optional($borrowRequest->borrowed_at)->toIso8601String(),
            'returned_at' => optional($borrowRequest->returned_at)->toIso8601String(),
            'items' => $borrowRequest->borrowItems->map(fn ($item) => [
                'id' => $item->id,
                'inventory_id' => $item->inventory_id,
                'item_code' => $item->inventory?->item_code,
                'name' => $item->inventory?->name,
                'brand' => $item->inventory?->brand,
                'unit' => $item->inventory?->unit,
                'quantity' => (int) $item->quantity,
                'condition_borrowed' => $item->condition_borrowed,
                'condition_returned' => $item->condition_returned,
                'is_returned' => (bool) $item->is_returned,
                'returned_at' => optional($item->returned_at)->toIso8601String(),
                'damage_notes' => $item->damage_notes,
                'return_evidence' => $item->returnEvidence ? [
                    'file_name' => $item->returnEvidence->file_name,
                    'uploaded_at' => optional($item->returnEvidence->uploaded_at)->toIso8601String(),
                ] : null,
            ]),
        ];
    }
}
