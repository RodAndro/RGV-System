<?php

namespace App\Services;

use App\Models\BorrowItem;
use App\Models\BorrowRequest;
use App\Models\BorrowReturnEvidence;
use App\Models\Inventory;
use App\Models\User;
use App\Notifications\BorrowRequestApproved;
use App\Notifications\BorrowRequestBorrowed;
use App\Notifications\BorrowRequestRejected;
use App\Notifications\BorrowRequestReturned;
use App\Notifications\NewBorrowRequestReceived;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BorrowService
{
    /**
     * Create a pending borrow request and reserve its stock so that other
     * employees cannot request the same items while awaiting review.
     *
     * @param  array<int, array{inventory_id: int, quantity: int}>  $items
     */
    public function createPending(User $employee, string $reason, string $borrowDate, string $dueDate, array $items): BorrowRequest
    {
        $normalized = $this->normalizeItems($items);

        return DB::transaction(function () use ($employee, $reason, $borrowDate, $dueDate, $normalized) {
            $inventories = Inventory::query()
                ->whereIn('id', $normalized->pluck('inventory_id')->unique()->sort()->values())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($normalized as $item) {
                $inventory = $inventories->get($item['inventory_id']);

                if (! $inventory) {
                    throw new DomainException('One of the selected items no longer exists.');
                }

                if (! $inventory->is_active || $inventory->status !== 'available') {
                    throw new DomainException(sprintf('%s is not available for borrowing.', $inventory->name));
                }

                if ($inventory->quantity < $item['quantity']) {
                    throw new DomainException(sprintf(
                        'Insufficient stock for %s. Only %d %s available.',
                        $inventory->name,
                        $inventory->quantity,
                        $inventory->unit,
                    ));
                }
            }

            $borrowRequest = BorrowRequest::create([
                'employee_id' => $employee->id,
                'status' => 'pending',
                'reason' => $reason,
                'borrow_date' => $borrowDate,
                'due_date' => $dueDate,
            ]);

            foreach ($normalized as $item) {
                $inventory = $inventories->get($item['inventory_id']);

                $borrowRequest->borrowItems()->create([
                    'inventory_id' => $item['inventory_id'],
                    'quantity' => $item['quantity'],
                    'reserved_quantity' => $item['quantity'],
                    'condition_borrowed' => 'good',
                ]);

                $inventory->decrement('quantity', $item['quantity']);
            }

            $this->notifyAdmins(new NewBorrowRequestReceived($borrowRequest));

            return $borrowRequest;
        });
    }

    public function approve(BorrowRequest $borrowRequest, User $admin): void
    {
        DB::transaction(function () use ($borrowRequest, $admin) {
            $request = $this->lockRequest($borrowRequest->id);

            if ($request->status !== 'pending') {
                throw new DomainException('Only pending borrow requests can be approved.');
            }

            $request->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);
        });

        $borrowRequest->employee?->notify(new BorrowRequestApproved($borrowRequest));
    }

    public function reject(BorrowRequest $borrowRequest, string $remarks): void
    {
        DB::transaction(function () use ($borrowRequest, $remarks) {
            $request = $this->lockRequest($borrowRequest->id);

            if (! in_array($request->status, ['pending', 'approved'], true)) {
                throw new DomainException('This borrow request can no longer be rejected.');
            }

            $this->releaseReservations($request);

            $request->update([
                'status' => 'rejected',
                'admin_remarks' => $remarks,
                'rejected_at' => now(),
            ]);
        });

        $borrowRequest->employee?->notify(new BorrowRequestRejected($borrowRequest));
    }

    public function cancel(BorrowRequest $borrowRequest, User $employee): void
    {
        if ($borrowRequest->employee_id !== $employee->id) {
            throw new DomainException('You may only cancel your own borrow requests.');
        }

        DB::transaction(function () use ($borrowRequest) {
            $request = $this->lockRequest($borrowRequest->id);

            if ($request->status !== 'pending') {
                throw new DomainException('Only pending borrow requests can be cancelled.');
            }

            $this->releaseReservations($request);

            $request->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        });
    }

    public function markBorrowed(BorrowRequest $borrowRequest): void
    {
        DB::transaction(function () use ($borrowRequest) {
            $request = $this->lockRequest($borrowRequest->id);

            if ($request->status !== 'approved') {
                throw new DomainException('Only approved borrow requests can be marked as borrowed.');
            }

            $request->update([
                'status' => 'borrowed',
                'borrowed_at' => now(),
            ]);
        });

        $borrowRequest->employee?->notify(new BorrowRequestBorrowed($borrowRequest));
    }

    /**
     * Mark selected items returned and restore stock exactly once.
     *
     * @param  array<int, array{borrow_item_id: int, condition_returned: string, damage_notes: ?string}>  $items
     */
    public function returnItems(BorrowRequest $borrowRequest, array $items, ?BorrowReturnEvidence $evidence = null): void
    {
        DB::transaction(function () use ($borrowRequest, $items, $evidence) {
            $request = $this->lockRequest($borrowRequest->id);

            if (! in_array($request->status, ['borrowed', 'returned'], true)) {
                throw new DomainException('Only currently borrowed requests can be returned.');
            }

            if ($request->status === 'returned') {
                return;
            }

            $borrowItemIds = collect($items)->pluck('borrow_item_id')->filter()->values();
            $borrowItems = BorrowItem::query()
                ->where('borrow_request_id', $request->id)
                ->whereIn('id', $borrowItemIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $borrowItem = $borrowItems->get($item['borrow_item_id'] ?? null);

                if (! $borrowItem) {
                    throw new DomainException('One of the selected items does not belong to this request.');
                }

                if ($borrowItem->is_returned) {
                    continue;
                }

                $restored = $borrowItem->reserved_quantity > 0 ? $borrowItem->reserved_quantity : $borrowItem->quantity;

                $borrowItem->update([
                    'condition_returned' => $item['condition_returned'] ?? $borrowItem->condition_borrowed,
                    'damage_notes' => $item['damage_notes'] ?? null,
                    'is_returned' => true,
                    'returned_at' => now(),
                    'return_evidence_id' => $evidence?->id,
                    'reserved_quantity' => 0,
                ]);

                if ($restored > 0) {
                    $borrowItem->inventory()?->first()?->increment('quantity', $restored);
                }
            }

            if ($request->borrowItems()->where('is_returned', false)->count() === 0) {
                $request->update([
                    'status' => 'returned',
                    'return_date' => now()->toDateString(),
                    'returned_at' => now(),
                ]);
            }
        });

        if ($borrowRequest->borrowItems()->where('is_returned', false)->count() === 0) {
            $borrowRequest->employee?->notify(new BorrowRequestReturned($borrowRequest));
        }
    }

    private function normalizeItems(array $items): Collection
    {
        $normalized = collect($items)
            ->filter(fn ($item) => ! empty($item['inventory_id']) && ! empty($item['quantity']))
            ->map(fn ($item) => [
                'inventory_id' => (int) $item['inventory_id'],
                'quantity' => (int) $item['quantity'],
            ])
            ->values();

        if ($normalized->isEmpty()) {
            throw new DomainException('Please select at least one item with a quantity.');
        }

        $duplicateIds = $normalized->pluck('inventory_id')->duplicates();
        if ($duplicateIds->isNotEmpty()) {
            throw new DomainException('Each inventory item can only be requested once per borrow request.');
        }

        return $normalized;
    }

    private function lockRequest(int $id): BorrowRequest
    {
        $request = BorrowRequest::whereKey($id)->lockForUpdate()->first();

        if (! $request) {
            throw new DomainException('Borrow request not found.');
        }

        return $request;
    }

    private function releaseReservations(BorrowRequest $borrowRequest): void
    {
        BorrowItem::query()
            ->where('borrow_request_id', $borrowRequest->id)
            ->lockForUpdate()
            ->get()
            ->each(function (BorrowItem $borrowItem) {
                if ($borrowItem->reserved_quantity > 0) {
                    $borrowItem->inventory?->increment('quantity', $borrowItem->reserved_quantity);
                    $borrowItem->update(['reserved_quantity' => 0]);
                }
            });
    }

    private function notifyAdmins($notification): void
    {
        User::role('admin')->get()->each->notify($notification);
    }
}
