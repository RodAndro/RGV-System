<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\BookingRejected;
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
