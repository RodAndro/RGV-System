<?php

namespace Tests\Unit;

use App\Services\BorrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        // Simulate a retry: calling return again must not restore stock twice.
        $this->service->returnItems($request, [
            ['borrow_item_id' => $item->id, 'condition_returned' => 'good', 'damage_notes' => null],
        ]);

        $this->assertSame(3, $inventory->fresh()->quantity);
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
