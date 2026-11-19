<?php

namespace Tests\Feature\Api\V1;

use App\Models\BorrowRequest;
use App\Models\InventoryCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class V1BorrowRequestTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->ensureRoles();

        foreach (['tools' => 'Tools', 'materials' => 'Materials', 'electrical' => 'Electrical', 'plumbing' => 'Plumbing', 'safety-equipment' => 'Safety Equipment'] as $slug => $name) {
            InventoryCategory::firstOrCreate(['slug' => $slug], ['name' => $name, 'is_active' => true]);
        }
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/borrow-requests')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_employee_can_create_a_borrow_request(): void
    {
        $employee = $this->createEmployee();
        Sanctum::actingAs($employee, ['access']);
        $inventory = $this->createInventory(['quantity' => 10, 'status' => 'available']);

        $response = $this->postJson('/api/v1/borrow-requests', [
            'reason' => 'Field inspection',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'items' => [['inventory_id' => $inventory->id, 'quantity' => 3]],
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.employee_id', $employee->id);

        $this->assertNotNull($response->headers->get('Location'));
        $this->assertDatabaseHas('inventories', ['id' => $inventory->id, 'quantity' => 7]);
    }

    public function test_create_validation_returns_the_error_envelope(): void
    {
        Sanctum::actingAs($this->createEmployee(), ['access']);

        $this->postJson('/api/v1/borrow-requests', [])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['error' => ['code', 'message', 'details']]);
    }

    public function test_create_fails_when_stock_is_insufficient(): void
    {
        Sanctum::actingAs($this->createEmployee(), ['access']);
        $inventory = $this->createInventory(['quantity' => 1, 'status' => 'available']);

        $this->postJson('/api/v1/borrow-requests', [
            'reason' => 'Field inspection',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'items' => [['inventory_id' => $inventory->id, 'quantity' => 5]],
        ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'UNPROCESSABLE_ENTITY');
    }

    public function test_employee_only_sees_their_own_requests(): void
    {
        $employee = $this->createEmployee();
        $other = $this->createEmployee();
        BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'pending',
            'reason' => 'mine',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
        ]);
        BorrowRequest::create([
            'employee_id' => $other->id,
            'status' => 'pending',
            'reason' => 'theirs',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        Sanctum::actingAs($employee, ['access']);

        $this->getJson('/api/v1/borrow-requests')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reason', 'mine')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_admin_sees_all_requests(): void
    {
        $employee = $this->createEmployee();
        BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'pending',
            'reason' => 'a',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        Sanctum::actingAs($this->createAdmin(), ['access']);

        $this->getJson('/api/v1/borrow-requests')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_employee_cannot_view_another_employees_request(): void
    {
        $employee = $this->createEmployee();
        $other = $this->createEmployee();
        $request = BorrowRequest::create([
            'employee_id' => $other->id,
            'status' => 'pending',
            'reason' => 'theirs',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        Sanctum::actingAs($employee, ['access']);

        $this->getJson('/api/v1/borrow-requests/'.$request->id)
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_admin_can_approve_a_request_via_status_patch(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $request = BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'pending',
            'reason' => 'approve me',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        Sanctum::actingAs($admin, ['access']);

        $this->patchJson('/api/v1/borrow-requests/'.$request->id, ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.approved_by', $admin->id);
    }

    public function test_employee_cannot_approve_a_request(): void
    {
        $employee = $this->createEmployee();
        $request = BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'pending',
            'reason' => 'nope',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        Sanctum::actingAs($employee, ['access']);

        $this->patchJson('/api/v1/borrow-requests/'.$request->id, ['status' => 'approved'])
            ->assertStatus(403);
    }

    public function test_admin_can_delete_a_borrow_request(): void
    {
        $employee = $this->createEmployee();
        $request = BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'pending',
            'reason' => 'delete me',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        Sanctum::actingAs($this->createAdmin(), ['access']);

        $this->deleteJson('/api/v1/borrow-requests/'.$request->id)
            ->assertNoContent();

        $this->assertSoftDeleted('borrow_requests', ['id' => $request->id]);
    }

    public function test_show_returns_404_for_missing_request(): void
    {
        Sanctum::actingAs($this->createAdmin(), ['access']);

        $this->getJson('/api/v1/borrow-requests/999999')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }
}
