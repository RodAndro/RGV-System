<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Services\BorrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'idempotency_key' => ['required', 'string', 'max:64'],
            'borrow_date' => ['required', 'date', 'after_or_equal:today'],
            'due_date' => ['required', 'date', 'after:borrow_date'],
            'reason' => ['required', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $requestHash = hash('sha256', json_encode([
            'borrow_date' => $data['borrow_date'],
            'due_date' => $data['due_date'],
            'reason' => $data['reason'],
            'items' => $data['items'],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $idempotencyKey = hash('sha256', 'laravel-api:'.trim($data['idempotency_key']));

        try {
            $response = DB::transaction(function () use ($request, $data, $requestHash, $idempotencyKey) {
                $inserted = DB::table('mobile_borrow_idempotency')->insertOrIgnore([
                    'employee_id' => $request->user()->id,
                    'idempotency_key' => $idempotencyKey,
                    'request_hash' => $requestHash,
                    'response_payload' => null,
                    'created_at' => now(),
                ]);

                if (! $inserted) {
                    $existing = DB::table('mobile_borrow_idempotency')
                        ->where('employee_id', $request->user()->id)
                        ->where('idempotency_key', $idempotencyKey)
                        ->lockForUpdate()
                        ->first();

                    if (! $existing) {
                        throw new \RuntimeException('The borrow idempotency record could not be loaded.');
                    }

                    if (! hash_equals(trim($existing->request_hash), $requestHash)) {
                        abort(409, 'This idempotency key was already used for a different request.');
                    }

                    if ($existing->response_payload === null) {
                        abort(409, 'This borrow request is still being processed.');
                    }

                    return json_decode(
                        (string) $existing->response_payload,
                        true,
                        flags: JSON_THROW_ON_ERROR,
                    );
                }

                $borrowRequest = $this->borrowService->createPending(
                    $request->user(),
                    $data['reason'],
                    $data['borrow_date'],
                    $data['due_date'],
                    $data['items'],
                );
                $response = [
                    'message' => 'Borrow request submitted.',
                    'request' => $this->requestPayload($borrowRequest->load('borrowItems.inventory')),
                ];

                DB::table('mobile_borrow_idempotency')
                    ->where('employee_id', $request->user()->id)
                    ->where('idempotency_key', $idempotencyKey)
                    ->update([
                        'response_payload' => json_encode($response, JSON_THROW_ON_ERROR),
                    ]);

                return $response;
            });
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json($response, 201);
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
                'status' => $borrowRequest->status,
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

        $payloadHash = hash('sha256', json_encode([
            'return_location' => $data['return_location'],
            'items' => $data['items'],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        try {
            $response = DB::transaction(function () use ($request, $borrowRequest, $data, $payloadHash) {
                // Serialize retries by request before checking/inserting the idempotency record.
                BorrowRequest::query()->whereKey($borrowRequest->id)->lockForUpdate()->firstOrFail();
                $existing = DB::table('mobile_return_idempotency')
                    ->where('employee_id', $request->user()->id)
                    ->where('borrow_request_id', $borrowRequest->id)
                    ->where('idempotency_key', $data['idempotency_key'])
                    ->first();

                if ($existing) {
                    $storedHash = json_decode((string) $existing->request_payload, true);
                    if (! is_string($storedHash) || ! hash_equals($storedHash, $payloadHash)) {
                        abort(409, 'This idempotency key was already used with different return details.');
                    }

                    return json_decode($existing->response_payload, true, flags: JSON_THROW_ON_ERROR);
                }

                $this->borrowService->returnItems($borrowRequest, $data['items'], null, $data['return_location'], $request->user());
                $borrowRequest->refresh()->load('borrowItems.inventory');
                $payload = [
                    'message' => 'Items returned successfully.',
                    'request' => $this->requestPayload($borrowRequest),
                ];

                DB::table('mobile_return_idempotency')->insert([
                    'employee_id' => $request->user()->id,
                    'borrow_request_id' => $borrowRequest->id,
                    'idempotency_key' => $data['idempotency_key'],
                    'request_payload' => json_encode($payloadHash),
                    'response_payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                ]);

                return $payload;
            });
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json($response);
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
