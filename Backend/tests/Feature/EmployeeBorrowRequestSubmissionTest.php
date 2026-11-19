<?php

namespace Tests\Feature;

use App\Models\BorrowRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class EmployeeBorrowRequestSubmissionTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_employee_can_submit_a_same_day_borrow_request(): void
    {
        Notification::fake();

        $employee = $this->createEmployee();
        $inventory = $this->createInventory(['quantity' => 5]);
        $date = now()->addDay()->toDateString();

        $this->actingAs($employee)
            ->followingRedirects()
            ->post(route('employee.borrow-requests.store'), [
                'reason' => 'Same-day equipment request',
                'borrow_date' => $date,
                'due_date' => $date,
                'items' => [[
                    'inventory_id' => $inventory->id,
                    'quantity' => 1,
                ]],
            ])
            ->assertOk()
            ->assertSee('Borrow request submitted successfully.');

        $borrowRequest = BorrowRequest::where('employee_id', $employee->id)->sole();

        $this->assertSame($date, $borrowRequest->borrow_date->toDateString());
        $this->assertSame($date, $borrowRequest->due_date->toDateString());
        $this->assertDatabaseHas('borrow_items', [
            'borrow_request_id' => $borrowRequest->id,
            'inventory_id' => $inventory->id,
            'quantity' => 1,
        ]);
        $this->assertSame(4, $inventory->fresh()->quantity);
    }
}
