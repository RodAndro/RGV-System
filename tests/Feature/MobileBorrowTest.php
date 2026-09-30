<?php

namespace Tests\Feature;

use App\Models\BorrowRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class MobileBorrowTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_borrow_request_reserves_stock_on_creation(): void
    {
        $employee = $this->createEmployee();
        $inventory = $this->createInventory(['item_code' => 'RGV-009', 'quantity' => 1]);

        Sanctum::actingAs($employee, ['employee']);

        $response = $this->postJson('/api/v1/mobile/borrow-requests', [
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'reason' => 'Site work',
            'items' => [['inventory_id' => $inventory->id, 'quantity' => 1]],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('request.status', 'pending')
            ->assertJsonStructure(['request' => ['request_number']]);

        $this->assertSame(0, $inventory->fresh()->quantity, 'Pending request must reserve the stock.');

        $this->assertDatabaseHas('borrow_items', [
            'inventory_id' => $inventory->id,
            'reserved_quantity' => 1,
        ]);
    }

    public function test_request_above_availability_is_rejected(): void
    {
        $employee = $this->createEmployee();
        $inventory = $this->createInventory(['item_code' => 'RGV-009', 'quantity' => 1]);

        Sanctum::actingAs($employee, ['employee']);

        $this->postJson('/api/v1/mobile/borrow-requests', [
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'reason' => 'Site work',
            'items' => [['inventory_id' => $inventory->id, 'quantity' => 2]],
        ])->assertStatus(409);
    }

    public function test_competing_reservations_fail(): void
    {
        $employeeA = $this->createEmployee();
        $employeeB = $this->createEmployee();
        $inventory = $this->createInventory(['quantity' => 1]);

        Sanctum::actingAs($employeeA, ['employee']);
        $this->postJson('/api/v1/mobile/borrow-requests', [
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'reason' => 'First',
            'items' => [['inventory_id' => $inventory->id, 'quantity' => 1]],
        ])->assertStatus(201);

        Sanctum::actingAs($employeeB, ['employee']);
        $this->postJson('/api/v1/mobile/borrow-requests', [
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'reason' => 'Second',
            'items' => [['inventory_id' => $inventory->id, 'quantity' => 1]],
        ])->assertStatus(409);
    }

    public function test_employee_can_cancel_pending_request(): void
    {
        $employee = $this->createEmployee();
        $inventory = $this->createInventory(['quantity' => 2]);

        Sanctum::actingAs($employee, ['employee']);

        $request = BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'pending',
            'reason' => 'Test',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $request->borrowItems()->create([
            'inventory_id' => $inventory->id,
            'quantity' => 2,
            'reserved_quantity' => 2,
        ]);

        $inventory->decrement('quantity', 2);

        $this->postJson("/api/v1/mobile/borrow-requests/{$request->id}/cancel")
            ->assertOk()
            ->assertJsonPath('request.status', 'cancelled');

        $this->assertSame(2, $inventory->fresh()->quantity, 'Cancellation must release the reservation.');
    }

    public function test_employee_cannot_access_another_employees_request(): void
    {
        $employeeA = $this->createEmployee();
        $employeeB = $this->createEmployee();

        $request = BorrowRequest::create([
            'employee_id' => $employeeA->id,
            'status' => 'pending',
            'reason' => 'Test',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        Sanctum::actingAs($employeeB, ['employee']);

        $this->getJson("/api/v1/mobile/borrow-requests/{$request->id}")->assertStatus(403);
    }
}
