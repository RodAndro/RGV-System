<?php

namespace Tests\Feature;

use App\Models\BorrowRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class AdminBorrowReturnTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_employee_borrow_request_detail_uses_the_shared_employee_layout(): void
    {
        $employee = $this->createEmployee();
        $borrowRequest = BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'borrowed',
            'reason' => 'Test',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'borrowed_at' => now(),
        ]);

        $this->actingAs($employee)
            ->get(route('employee.borrow-requests.show', $borrowRequest))
            ->assertOk()
            ->assertSee('RGV Employee')
            ->assertSee('Borrow Request Details')
            ->assertSee('My Requests')
            ->assertSee('sidebar-link-active-mantis', false)
            ->assertSee('Return Items');
    }

    public function test_admin_enters_a_return_location_and_cannot_return_without_it(): void
    {
        Notification::fake();

        $employee = $this->createEmployee();
        $inventory = $this->createInventory(['quantity' => 0]);
        $borrowRequest = BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'borrowed',
            'reason' => 'Test',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'borrowed_at' => now(),
        ]);
        $item = $borrowRequest->borrowItems()->create([
            'inventory_id' => $inventory->id,
            'quantity' => 1,
            'reserved_quantity' => 1,
            'is_returned' => false,
        ]);

        $this->actingAs($this->createAdmin());

        $this->get(route('admin.borrow-requests.show', $borrowRequest))
            ->assertOk()
            ->assertSee('type="text" name="return_location"', false)
            ->assertDontSee('<select name="return_location"', false);

        $returnRoute = route('admin.borrow-returns.return', $borrowRequest);
        $this->post($returnRoute, [])
            ->assertSessionHasErrors('return_location');
        $this->assertSame('borrowed', $borrowRequest->fresh()->status);
        $this->assertFalse((bool) $item->fresh()->is_returned);

        $location = 'The items were returned to the maintenance workshop.';
        $this->post($returnRoute, ['return_location' => $location])
            ->assertRedirect();

        $this->assertSame('returned', $borrowRequest->fresh()->status);
        $this->assertSame($location, $borrowRequest->fresh()->return_location);
        $this->assertTrue((bool) $item->fresh()->is_returned);
    }
}
