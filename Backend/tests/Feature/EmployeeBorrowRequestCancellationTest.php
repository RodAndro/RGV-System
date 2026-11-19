<?php

namespace Tests\Feature;

use App\Services\BorrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class EmployeeBorrowRequestCancellationTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_employee_can_cancel_a_pending_request_from_its_detail_page(): void
    {
        Notification::fake();

        $employee = $this->createEmployee();
        $inventory = $this->createInventory(['quantity' => 5]);
        $borrowRequest = app(BorrowService::class)->createPending(
            $employee,
            'Need this equipment',
            now()->addDay()->toDateString(),
            now()->addDays(2)->toDateString(),
            [['inventory_id' => $inventory->id, 'quantity' => 2]],
        );

        $this->actingAs($employee)
            ->get(route('employee.borrow-requests.show', $borrowRequest))
            ->assertOk()
            ->assertSee('Cancel Request');

        $this->post(route('employee.borrow-requests.cancel', $borrowRequest))
            ->assertRedirect(route('employee.borrow-requests.show', $borrowRequest))
            ->assertSessionHas('success', 'Borrow request cancelled successfully. Reserved items are available again.');

        $this->assertSame('cancelled', $borrowRequest->fresh()->status);
        $this->assertNotNull($borrowRequest->fresh()->cancelled_at);
        $this->assertSame(5, $inventory->fresh()->quantity);
    }

    public function test_employee_cannot_cancel_another_employees_request(): void
    {
        Notification::fake();

        $owner = $this->createEmployee();
        $otherEmployee = $this->createEmployee();
        $inventory = $this->createInventory(['quantity' => 5]);
        $borrowRequest = app(BorrowService::class)->createPending(
            $owner,
            'Need this equipment',
            now()->addDay()->toDateString(),
            now()->addDays(2)->toDateString(),
            [['inventory_id' => $inventory->id, 'quantity' => 2]],
        );

        $this->actingAs($otherEmployee)
            ->post(route('employee.borrow-requests.cancel', $borrowRequest))
            ->assertForbidden();

        $this->assertSame('pending', $borrowRequest->fresh()->status);
        $this->assertSame(3, $inventory->fresh()->quantity);
    }
}
