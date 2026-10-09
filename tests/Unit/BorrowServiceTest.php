<?php

namespace Tests\Unit;

use App\Notifications\BorrowRequestReturned;
use App\Notifications\LowStockAlert;
use App\Notifications\NewBorrowRequestReceived;
use App\Services\BorrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class BorrowServiceTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    private BorrowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->service = new BorrowService;
    }

    public function test_creating_pending_request_reserves_stock(): void
    {
        $employee = $this->createEmployee();
        $inventory = $this->createInventory(['quantity' => 5]);

        $request = $this->service->createPending($employee, 'Need it', '2026-10-01', '2026-10-10', [
            ['inventory_id' => $inventory->id, 'quantity' => 3],
        ]);

        $this->assertSame('pending', $request->status);
        $this->assertSame(2, $inventory->fresh()->quantity);

        $item = $request->borrowItems()->first();
        $this->assertSame(3, $item->reserved_quantity);
    }

    public function test_approval_does_not_decrement_again_and_rejection_releases(): void
    {
        $employee = $this->createEmployee();
        $admin = $this->createAdmin();
        $inventory = $this->createInventory(['quantity' => 5]);

        $request = $this->service->createPending($employee, 'Need it', '2026-10-01', '2026-10-10', [
            ['inventory_id' => $inventory->id, 'quantity' => 3],
        ]);

        $this->assertSame(2, $inventory->fresh()->quantity);

        $this->service->approve($request, $admin);
        $this->assertSame(2, $inventory->fresh()->quantity, 'Approval must not decrement stock again.');
        $this->assertSame('approved', $request->fresh()->status);

        $this->service->reject($request, 'Not needed');
        $this->assertSame(5, $inventory->fresh()->quantity, 'Rejection must release the reservation.');
        $this->assertSame('rejected', $request->fresh()->status);
        $this->assertSame(0, $request->borrowItems()->first()->fresh()->reserved_quantity);
    }

    public function test_cancellation_releases_reservation(): void
    {
        $employee = $this->createEmployee();
        $inventory = $this->createInventory(['quantity' => 2]);

        $request = $this->service->createPending($employee, 'Need it', '2026-10-01', '2026-10-10', [
            ['inventory_id' => $inventory->id, 'quantity' => 2],
        ]);

        $this->assertSame(0, $inventory->fresh()->quantity);

        $this->service->cancel($request, $employee);

        $this->assertSame('cancelled', $request->fresh()->status);
        $this->assertSame(2, $inventory->fresh()->quantity);
    }

    public function test_new_request_notification_is_not_sent_when_an_outer_transaction_rolls_back(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $inventory = $this->createInventory([
            'quantity' => 2,
            'low_stock_threshold' => 1,
        ]);

        try {
            DB::transaction(function () use ($employee, $inventory): void {
                $this->service->createPending($employee, 'Need it', '2026-10-01', '2026-10-10', [
                    ['inventory_id' => $inventory->id, 'quantity' => 1],
                ]);

                throw new \RuntimeException('Roll back the outer transaction.');
            });
        } catch (\RuntimeException) {
        }

        Notification::assertNotSentTo($admin, NewBorrowRequestReceived::class);
    }

    public function test_reserving_stock_only_alerts_admins_when_quantity_crosses_low_stock_threshold(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $inventory = $this->createInventory([
            'quantity' => 5,
            'low_stock_threshold' => 3,
        ]);

        $this->service->createPending($employee, 'Need it', '2026-10-01', '2026-10-10', [
            ['inventory_id' => $inventory->id, 'quantity' => 2],
        ]);
        Notification::assertSentToTimes($admin, LowStockAlert::class, 1);

        $this->service->createPending($employee, 'Need more', '2026-10-01', '2026-10-10', [
            ['inventory_id' => $inventory->id, 'quantity' => 1],
        ]);
        Notification::assertSentToTimes($admin, LowStockAlert::class, 1);
    }

    public function test_competing_reservations_are_rejected(): void
    {
        $employeeA = $this->createEmployee();
        $employeeB = $this->createEmployee();
        $inventory = $this->createInventory(['quantity' => 1]);

        $this->service->createPending($employeeA, 'A', '2026-10-01', '2026-10-10', [
            ['inventory_id' => $inventory->id, 'quantity' => 1],
        ]);

        $this->expectException(\DomainException::class);

        $this->service->createPending($employeeB, 'B', '2026-10-01', '2026-10-10', [
            ['inventory_id' => $inventory->id, 'quantity' => 1],
        ]);
    }

    public function test_return_restores_stock_exactly_once(): void
    {
        $employee = $this->createEmployee();
        $admin = $this->createAdmin();
        $inventory = $this->createInventory(['quantity' => 3]);

        $request = $this->service->createPending($employee, 'Need it', '2026-10-01', '2026-10-10', [
            ['inventory_id' => $inventory->id, 'quantity' => 3],
        ]);
        $this->service->approve($request, $admin);
        $this->service->markBorrowed($request);

        $item = $request->borrowItems()->first();

        $this->service->returnItems($request, [
            ['borrow_item_id' => $item->id, 'condition_returned' => 'good', 'damage_notes' => null],
        ]);

        $this->assertSame(3, $inventory->fresh()->quantity);
        $this->assertTrue((bool) $item->fresh()->is_returned);
        $this->assertSame('returned', $request->fresh()->status);
        Notification::assertSentToTimes($employee, BorrowRequestReturned::class, 1);

        // Simulate a retry: calling return again must not restore stock twice.
        $this->service->returnItems($request, [
            ['borrow_item_id' => $item->id, 'condition_returned' => 'good', 'damage_notes' => null],
        ]);

        $this->assertSame(3, $inventory->fresh()->quantity);
        Notification::assertSentToTimes($employee, BorrowRequestReturned::class, 1);
    }

    public function test_quantity_above_availability_throws(): void
    {
        $employee = $this->createEmployee();
        $inventory = $this->createInventory(['quantity' => 1]);

        $this->expectException(\DomainException::class);

        $this->service->createPending($employee, 'Too much', '2026-10-01', '2026-10-10', [
            ['inventory_id' => $inventory->id, 'quantity' => 2],
        ]);
    }
}
