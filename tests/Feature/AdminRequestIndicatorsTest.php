<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\BookingAssigned;
use App\Notifications\BookingRejected;
use App\Notifications\NewBookingReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Str;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class AdminRequestIndicatorsTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_admin_dashboard_renders_with_cached_summary_data(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $this->assertSame(0, $response->viewData('stats')['total_bookings']);

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_management_and_report_pages_render(): void
    {
        $this->actingAs($this->createAdmin());

        foreach ([
            'admin.bookings.index',
            'admin.inventories.index',
            'admin.borrow-requests.index',
            'admin.users.index',
            'admin.reports.index',
            'admin.reports.bookings',
            'admin.reports.inventory',
            'admin.reports.borrow-requests',
            'admin.reports.users',
            'admin.trash.index',
            'admin.audit.index',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_employee_dashboard_and_inventory_pages_render(): void
    {
        $this->actingAs($this->createEmployee());

        $this->get(route('employee.dashboard'))->assertOk();
        $this->get(route('employee.inventories.index'))->assertOk();
    }

    public function test_employee_notifications_use_the_shared_employee_layout(): void
    {
        $employee = $this->createEmployee();
        $this->createNotification($employee, 'booking');

        $this->actingAs($employee)
            ->get(route('employee.notifications.index'))
            ->assertOk()
            ->assertSee('RGV Employee')
            ->assertSee('Assigned Work')
            ->assertSee('Available Inventory')
            ->assertSee('All Notifications')
            ->assertSee('New request');
    }

    public function test_admin_notifications_render_with_admin_actions(): void
    {
        $admin = $this->createAdmin();
        $this->createNotification($admin, 'booking');

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('All Notifications')
            ->assertSee('New request')
            ->assertSee('Clear All');
    }

    public function test_database_notifications_reach_both_roles_and_open_the_correct_booking_route(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $booking = Booking::factory()->create(['employee_id' => $employee->id]);

        foreach ([$admin, $employee] as $user) {
            NotificationPreference::create([
                'user_id' => $user->id,
                'type' => 'booking',
                'email_enabled' => false,
                'in_app_enabled' => true,
            ]);
        }

        $admin->notify(new NewBookingReceived($booking));
        $employee->notify(new BookingAssigned($booking));

        $adminNotification = $admin->notifications()->firstOrFail();
        $employeeNotification = $employee->notifications()->firstOrFail();

        $this->assertSame('booking', $adminNotification->type);
        $this->assertSame('booking', $employeeNotification->type);

        $this->actingAs($admin)
            ->getJson(route('admin.notifications.unread-count'))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('request_updates.work_request', true);

        $this->actingAs($employee)
            ->getJson(route('employee.notifications.unread-count'))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('notifications.0.link', route('employee.bookings.show', $booking));

        $this->get(route('employee.notifications.open', $employeeNotification->id))
            ->assertRedirect(route('employee.bookings.show', $booking));

        $this->assertTrue($employeeNotification->fresh()->is_read);
        $this->assertNotNull($employeeNotification->fresh()->read_at);
    }

    public function test_notification_actions_cannot_change_another_users_notifications(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $otherEmployee = $this->createEmployee();
        $adminNotification = $this->createNotification($admin, 'booking');
        $employeeNotification = $this->createNotification($employee, 'booking');

        $this->actingAs($otherEmployee)
            ->post(route('employee.notifications.mark-read', $employeeNotification->id))
            ->assertNotFound();

        $this->assertFalse($employeeNotification->fresh()->is_read);

        $this->actingAs($admin)
            ->post(route('admin.notifications.clear-all'))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', ['id' => $adminNotification->id]);
        $this->assertDatabaseHas('notifications', ['id' => $employeeNotification->id]);
    }

    public function test_employee_inventory_pages_return_only_the_selected_page_size(): void
    {
        $employee = $this->createEmployee();

        foreach (range(1, 12) as $index) {
            $this->createInventory([
                'item_code' => sprintf('AVAILABLE-%03d', $index),
                'quantity' => 10,
                'low_stock_threshold' => 2,
            ]);
            $this->createInventory([
                'item_code' => sprintf('LOW-STOCK-%03d', $index),
                'quantity' => 1,
                'low_stock_threshold' => 5,
            ]);
        }

        $this->actingAs($employee);

        $availableDefaultResponse = $this->get(route('employee.inventories.index'))->assertOk();
        $availableDefaultItems = $availableDefaultResponse->viewData('inventories');
        $this->assertSame(10, $availableDefaultItems->perPage());
        $this->assertCount(10, $availableDefaultItems->items());

        $availableResponse = $this->get(route('employee.inventories.index', ['per_page' => 10]))->assertOk();
        $availableItems = $availableResponse->viewData('inventories');
        $this->assertSame(10, $availableItems->perPage());
        $this->assertCount(10, $availableItems->items());

        $lowStockDefaultResponse = $this->get(route('employee.inventories.low-stock'))->assertOk();
        $lowStockDefaultItems = $lowStockDefaultResponse->viewData('inventories');
        $this->assertSame(10, $lowStockDefaultItems->perPage());
        $this->assertCount(10, $lowStockDefaultItems->items());

        $lowStockResponse = $this->get(route('employee.inventories.low-stock', ['per_page' => 10]))->assertOk();
        $lowStockItems = $lowStockResponse->viewData('inventories');
        $this->assertSame(10, $lowStockItems->perPage());
        $this->assertCount(10, $lowStockItems->items());
    }

    public function test_request_indicators_clear_when_the_matching_admin_list_is_opened(): void
    {
        $admin = $this->createAdmin();
        $workRequestNotification = $this->createNotification($admin, 'booking');
        $borrowRequestNotification = $this->createNotification($admin, 'borrow_request');

        $this->actingAs($admin)
            ->getJson(route('admin.notifications.unread-count'))
            ->assertOk()
            ->assertJsonPath('request_updates.work_request', true)
            ->assertJsonPath('request_updates.borrow_request', true);

        $this->get(route('admin.bookings.index'))->assertOk();
        $this->assertNotNull($workRequestNotification->fresh()->read_at);
        $this->assertNull($borrowRequestNotification->fresh()->read_at);

        $this->getJson(route('admin.notifications.unread-count'))
            ->assertJsonPath('request_updates.work_request', false)
            ->assertJsonPath('request_updates.borrow_request', true);

        $this->get(route('admin.borrow-requests.index'))->assertOk();
        $this->assertNotNull($borrowRequestNotification->fresh()->read_at);

        $this->getJson(route('admin.notifications.unread-count'))
            ->assertJsonPath('request_updates.work_request', false)
            ->assertJsonPath('request_updates.borrow_request', false);
    }

    public function test_mark_all_read_updates_notifications_for_admins_and_employees(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $adminNotification = $this->createNotification($admin, 'booking');
        $employeeNotification = $this->createNotification($employee, 'booking');

        $this->actingAs($admin)
            ->post(route('admin.notifications.mark-all-read'))
            ->assertRedirect();
        $this->assertNotNull($adminNotification->fresh()->read_at);

        $this->actingAs($employee)
            ->post(route('employee.notifications.mark-all-read'))
            ->assertRedirect();
        $this->assertNotNull($employeeNotification->fresh()->read_at);
    }

    public function test_rejected_guest_booking_only_uses_the_mail_channel(): void
    {
        $notification = new BookingRejected(Booking::factory()->create());

        $this->assertSame(['mail'], $notification->via(new AnonymousNotifiable));

        $employee = $this->createEmployee();
        NotificationPreference::create([
            'user_id' => $employee->id,
            'type' => 'booking',
            'email_enabled' => false,
            'in_app_enabled' => true,
        ]);
        $this->assertSame(['database'], $notification->via($employee));
    }

    public function test_opening_a_bell_notification_marks_it_read_and_returns_an_open_route(): void
    {
        $admin = $this->createAdmin();
        $notification = $this->createNotification($admin, 'booking');

        $this->actingAs($admin)
            ->getJson(route('admin.notifications.unread-count'))
            ->assertOk()
            ->assertJsonPath('notifications.0.open_url', route('admin.notifications.open', $notification->id));

        $this->get(route('admin.notifications.open', $notification->id))
            ->assertRedirect(route('admin.notifications.index'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    private function createNotification(User $admin, string $type)
    {
        return $admin->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'title' => 'New request',
            'message' => 'A new request was submitted.',
            'link' => route('admin.notifications.index'),
        ]);
    }
}
