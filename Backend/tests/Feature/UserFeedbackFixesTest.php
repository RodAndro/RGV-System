<?php

namespace Tests\Feature;

use App\Exports\BookingsExport;
use App\Exports\InventoryExport;
use App\Models\Booking;
use App\Models\BorrowRequest;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class UserFeedbackFixesTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_user_can_submit_multiple_bookings_and_each_creates_a_new_record(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'full_name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'contact_number' => '09171234567',
            'address' => '123 Main St, City',
            'preferred_date' => Carbon::tomorrow()->toDateString(),
            'preferred_time' => '10:00',
            'purpose_category' => 'consultation',
            'reason' => 'First booking request',
        ];

        // First submission
        $response1 = $this->post(route('booking.store'), $payload);
        $response1->assertRedirect();

        $booking1 = Booking::where('email', 'john.doe@example.com')->firstOrFail();
        $response1->assertRedirect(route('booking.track', $booking1->reference_number));

        // Second submission by same customer (should create a second distinct record, not redirect to first)
        $payload['reason'] = 'Second booking request';
        $response2 = $this->post(route('booking.store'), $payload);
        $response2->assertRedirect();

        $this->assertSame(2, Booking::where('email', 'john.doe@example.com')->count());

        $booking2 = Booking::where('email', 'john.doe@example.com')
            ->where('id', '!=', $booking1->id)
            ->firstOrFail();

        $this->assertNotEquals($booking1->reference_number, $booking2->reference_number);
        $this->assertSame('First booking request', $booking1->reason);
        $this->assertSame('Second booking request', $booking2->reason);
        $response2->assertRedirect(route('booking.track', $booking2->reference_number));
    }

    public function test_trashed_records_appear_in_reports_with_status_removed(): void
    {
        $admin = $this->createAdmin();

        $category = InventoryCategory::create([
            'name' => 'Tools',
            'slug' => 'tools',
        ]);

        $activeBooking = Booking::factory()->create([
            'status' => 'pending',
            'reference_number' => 'BK-ACTIVE-001',
        ]);

        $deletedBooking = Booking::factory()->create([
            'status' => 'approved',
            'reference_number' => 'BK-DELETED-001',
        ]);
        $deletedBooking->delete();

        $activeItem = Inventory::create([
            'item_code' => 'ITM-ACTIVE-01',
            'name' => 'Active Drill',
            'category_id' => $category->id,
            'quantity' => 5,
            'unit' => 'pcs',
            'unit_cost' => 100,
            'status' => 'available',
            'condition' => 'good',
        ]);

        $deletedItem = Inventory::create([
            'item_code' => 'ITM-DELETED-01',
            'name' => 'Deleted Drill',
            'category_id' => $category->id,
            'quantity' => 2,
            'unit' => 'pcs',
            'unit_cost' => 100,
            'status' => 'available',
            'condition' => 'good',
        ]);
        $deletedItem->delete();

        // 1. Web reports show both active and deleted records, with Removed badge
        $this->actingAs($admin)
            ->get(route('admin.reports.bookings'))
            ->assertOk()
            ->assertSee('BK-ACTIVE-001')
            ->assertSee('BK-DELETED-001')
            ->assertSee('Removed');

        $this->actingAs($admin)
            ->get(route('admin.reports.inventory'))
            ->assertOk()
            ->assertSee('ITM-ACTIVE-01')
            ->assertSee('ITM-DELETED-01')
            ->assertSee('Removed');

        // 2. Export mapping outputs status 'Removed' for trashed records
        $bookingsExport = new BookingsExport();
        $mappedDeletedBooking = $bookingsExport->map($deletedBooking);
        $this->assertContains('Removed', $mappedDeletedBooking);

        $inventoryExport = new InventoryExport();
        $mappedDeletedItem = $inventoryExport->map($deletedItem);
        $this->assertContains('Removed', $mappedDeletedItem);
    }

    public function test_borrow_requests_status_summary_and_filter_include_overdue_and_exclude_rejected(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();

        // Active borrowed request (not overdue)
        BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'borrowed',
            'reason' => 'Current task',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        // Overdue borrowed request
        BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'borrowed',
            'reason' => 'Late item',
            'borrow_date' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.borrow-requests.index'));

        $response->assertOk();
        // Overdue card in status summary
        $response->assertSee('Overdue');
        // Filter dropdown has overdue option and no rejected option
        $response->assertSee('<option value="overdue"', false);
        $response->assertDontSee('<option value="rejected"', false);

        // Filter by overdue
        $filterResponse = $this->actingAs($admin)
            ->get(route('admin.borrow-requests.index', ['status' => 'overdue']));

        $filterResponse->assertOk()
            ->assertSee('Late item')
            ->assertDontSee('Current task');
    }
}
