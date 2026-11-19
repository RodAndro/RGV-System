<?php

namespace Tests\Feature\Api;

use App\Models\BorrowRequest;
use App\Models\InventoryCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

/**
 * Covers the endpoints the Flutter app calls, including the persisted
 * dashboard dismissal.
 */
class MobileDashboardTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->ensureRoles();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->getJson('/api/v1/mobile/dashboard')->assertStatus(401);
    }

    public function test_dashboard_returns_summary_and_recent_requests(): void
    {
        $employee = $this->createEmployee();
        Sanctum::actingAs($employee, ['employee']);

        BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'pending',
            'reason' => 'Site visit',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->getJson('/api/v1/mobile/dashboard')
            ->assertOk()
            ->assertJsonPath('summary.total', 1)
            ->assertJsonPath('summary.pending', 1)
            ->assertJsonPath('employee.id', $employee->id)
            ->assertJsonCount(1, 'recent_requests');
    }

    public function test_employee_can_dismiss_a_recent_request(): void
    {
        $employee = $this->createEmployee();
        Sanctum::actingAs($employee, ['employee']);

        $request = BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'pending',
            'reason' => 'Dismiss me',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->postJson("/api/v1/mobile/dashboard/recent-requests/{$request->id}/dismiss")
            ->assertOk()
            ->assertJsonPath('dismissed', true)
            ->assertJsonPath('request_id', $request->id);

        // The dismissed request is persisted and hidden from the dashboard.
        $this->assertDatabaseHas('mobile_recent_request_dismissals', [
            'employee_id' => $employee->id,
            'borrow_request_id' => $request->id,
        ]);

        $this->getJson('/api/v1/mobile/dashboard')
            ->assertOk()
            ->assertJsonCount(0, 'recent_requests')
            ->assertJsonPath('summary.total', 1);
    }

    public function test_employee_cannot_dismiss_another_employees_request(): void
    {
        $employee = $this->createEmployee();
        $other = $this->createEmployee();

        $request = BorrowRequest::create([
            'employee_id' => $other->id,
            'status' => 'pending',
            'reason' => 'Not yours',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        Sanctum::actingAs($employee, ['employee']);

        $this->postJson("/api/v1/mobile/dashboard/recent-requests/{$request->id}/dismiss")
            ->assertStatus(404);
    }

    public function test_admin_created_employee_can_use_the_mobile_api(): void
    {
        $employee = $this->createEmployee([
            'email' => 'selfhosted.employee@example.test',
        ]);

        $this->postJson('/api/v1/mobile/auth/login', [
            'email' => $employee->email,
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['token', 'user']);

        $token = $this->postJson('/api/v1/mobile/auth/login', [
            'email' => $employee->email,
            'password' => 'password',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/mobile/dashboard')
            ->assertOk()
            ->assertJsonPath('employee.id', $employee->id);
    }

    public function test_inventory_search_returns_active_items(): void
    {
        foreach (['tools' => 'Tools'] as $slug => $name) {
            InventoryCategory::firstOrCreate(['slug' => $slug], ['name' => $name, 'is_active' => true]);
        }

        $employee = $this->createEmployee();
        Sanctum::actingAs($employee, ['employee']);
        $this->createInventory(['name' => 'Pipe Wrench', 'item_code' => 'RGV-777']);

        $this->getJson('/api/v1/mobile/inventory/search?q=Pipe')
            ->assertOk()
            ->assertJsonPath('items.0.name', 'Pipe Wrench');

        $this->getJson('/api/v1/mobile/inventory/lookup?code=RGV-777')
            ->assertOk()
            ->assertJsonPath('item.item_code', 'RGV-777');
    }
}
