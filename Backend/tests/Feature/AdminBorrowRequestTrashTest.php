<?php

namespace Tests\Feature;

use App\Models\BorrowRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class AdminBorrowRequestTrashTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_admin_can_move_returned_borrow_request_to_trash_and_restore_it(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $borrowRequest = BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'returned',
            'reason' => 'Project work',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'return_date' => now()->toDateString(),
            'returned_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.borrow-requests.destroy', $borrowRequest))
            ->assertRedirect(route('admin.borrow-requests.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('borrow_requests', ['id' => $borrowRequest->id]);

        $this->get(route('admin.trash.index', ['type' => 'borrow_request']))
            ->assertOk()
            ->assertSee($borrowRequest->request_number)
            ->assertSee('Restore')
            ->assertDontSee('Delete Permanently')
            ->assertDontSee('Delete Selected Permanently');

        $this->post(route('admin.trash.restore', ['type' => 'borrow_request', 'id' => $borrowRequest->id]))
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted('borrow_requests', ['id' => $borrowRequest->id]);
    }

    public function test_admin_cannot_move_unreturned_borrow_request_to_trash(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $borrowRequest = BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'borrowed',
            'reason' => 'Project work',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.borrow-requests.index'))
            ->delete(route('admin.borrow-requests.destroy', $borrowRequest))
            ->assertRedirect(route('admin.borrow-requests.index'))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted('borrow_requests', ['id' => $borrowRequest->id]);
    }

    public function test_permanent_delete_is_disabled(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $borrowRequest = $this->createTrashedBorrowRequest($employee->id);
        $trashUrl = route('admin.trash.index', ['type' => 'borrow_request']);

        $this->actingAs($admin)
            ->from($trashUrl)
            ->delete(route('admin.trash.force-delete', ['type' => 'borrow_request', 'id' => $borrowRequest->id]), [
                'password' => 'password',
            ])
            ->assertRedirect($trashUrl)
            ->assertSessionHas('error', 'Permanent deletion has been disabled.');

        $this->assertSoftDeleted('borrow_requests', ['id' => $borrowRequest->id]);
    }

    public function test_bulk_permanent_delete_is_disabled(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $borrowRequest = $this->createTrashedBorrowRequest($employee->id);
        $trashUrl = route('admin.trash.index', ['type' => 'borrow_request']);

        $this->actingAs($admin)
            ->from($trashUrl)
            ->delete(route('admin.trash.force-delete-selected', ['type' => 'borrow_request']), [
                'ids' => [$borrowRequest->id],
                'password' => 'password',
            ])
            ->assertRedirect($trashUrl)
            ->assertSessionHas('error', 'Permanent deletion has been disabled.');

        $this->assertSoftDeleted('borrow_requests', ['id' => $borrowRequest->id]);
    }

    private function createTrashedBorrowRequest(int $employeeId): BorrowRequest
    {
        $borrowRequest = BorrowRequest::create([
            'employee_id' => $employeeId,
            'status' => 'returned',
            'reason' => 'Project work',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'return_date' => now()->toDateString(),
            'returned_at' => now(),
        ]);

        $borrowRequest->delete();

        return $borrowRequest;
    }
}
