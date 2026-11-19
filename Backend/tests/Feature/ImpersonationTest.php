<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BorrowRequest;
use App\Models\NotificationPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_impersonating_an_employee_redirects_to_employee_dashboard_and_can_be_stopped(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();

        $this->actingAs($admin)
            ->post(route('admin.users.impersonate', $employee), ['password' => 'password'])
            ->assertRedirect(route('employee.dashboard'));

        $this->assertAuthenticatedAs($employee);
        $this->get(route('admin.users.show', $employee))
            ->assertRedirect(route('employee.dashboard'));

        $this->get(route('employee.dashboard'))
            ->assertOk()
            ->assertSeeText('Impersonating ' . $employee->name)
            ->assertSee(route('impersonation.stop'));

        $this->post(route('impersonation.stop'))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertFalse(session()->has('impersonate.original_id'));
    }

    public function test_logout_is_disabled_and_blocked_until_impersonation_is_stopped(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();

        $this->actingAs($admin)
            ->post(route('admin.users.impersonate', $employee), ['password' => 'password'])
            ->assertRedirect(route('employee.dashboard'));

        $this->get(route('employee.dashboard'))
            ->assertOk()
            ->assertSee('Stop Impersonating')
            ->assertSee('disabled', false);

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Stop Impersonating')
            ->assertSee('aria-disabled="true"', false);

        $this->post(route('logout'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'Stop impersonating before logging out of your account.');

        $this->assertAuthenticatedAs($employee);
        $this->assertSame($admin->id, session('impersonate.original_id'));

        $this->post(route('impersonation.stop'))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertFalse(session()->has('impersonate.original_id'));
    }

    public function test_impersonating_an_admin_redirects_to_admin_dashboard(): void
    {
        $originalAdmin = $this->createAdmin();
        $targetAdmin = $this->createAdmin();
        $originalAdmin->notifications()->create([
            'type' => 'system',
            'title' => 'Original admin notification',
            'message' => 'Original account only',
        ]);
        $targetAdmin->notifications()->create([
            'type' => 'system',
            'title' => 'Target admin notification',
            'message' => 'Target account only',
        ]);

        $this->actingAs($originalAdmin)
            ->post(route('admin.users.impersonate', $targetAdmin), ['password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($targetAdmin);
        $this->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Target admin notification')
            ->assertDontSee('Original admin notification');
        $targetProfile = $this->get(route('profile.edit'))
            ->assertOk()
            ->viewData('user');
        $this->assertTrue($targetProfile->is($targetAdmin));

        $this->post(route('impersonation.stop'))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($originalAdmin);
        $this->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Original admin notification')
            ->assertDontSee('Target admin notification');
        $originalProfile = $this->get(route('profile.edit'))
            ->assertOk()
            ->viewData('user');
        $this->assertTrue($originalProfile->is($originalAdmin));
    }

    public function test_impersonated_employee_only_accesses_their_own_account_data(): void
    {
        $admin = $this->createAdmin(['name' => 'Original Admin']);
        $employee = $this->createEmployee(['name' => 'Employee One']);
        $otherEmployee = $this->createEmployee(['name' => 'Employee Two']);

        $ownBooking = Booking::factory()->create([
            'reference_number' => 'IMPERSONATION-OWN-BOOKING',
            'employee_id' => $employee->id,
        ]);
        $otherBooking = Booking::factory()->create([
            'reference_number' => 'IMPERSONATION-OTHER-BOOKING',
            'employee_id' => $otherEmployee->id,
        ]);
        $ownBorrowRequest = BorrowRequest::create([
            'request_number' => 'IMPERSONATION-OWN-BORROW',
            'employee_id' => $employee->id,
            'status' => 'pending',
            'reason' => 'Own employee request',
            'borrow_date' => today()->toDateString(),
            'due_date' => today()->addDays(7)->toDateString(),
        ]);
        $otherBorrowRequest = BorrowRequest::create([
            'request_number' => 'IMPERSONATION-OTHER-BORROW',
            'employee_id' => $otherEmployee->id,
            'status' => 'pending',
            'reason' => 'Other employee request',
            'borrow_date' => today()->toDateString(),
            'due_date' => today()->addDays(7)->toDateString(),
        ]);
        NotificationPreference::create([
            'user_id' => $employee->id,
            'type' => 'booking',
            'email_enabled' => false,
            'in_app_enabled' => true,
        ]);
        NotificationPreference::create([
            'user_id' => $otherEmployee->id,
            'type' => 'booking',
            'email_enabled' => true,
            'in_app_enabled' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.impersonate', $employee), ['password' => 'password'])
            ->assertRedirect(route('employee.dashboard'));

        $this->assertAuthenticatedAs($employee);

        $bookings = $this->get(route('employee.bookings.index'))
            ->assertOk()
            ->viewData('bookings');
        $this->assertSame([$ownBooking->id], $bookings->getCollection()->modelKeys());

        $borrowRequests = $this->get(route('employee.borrow-requests.index'))
            ->assertOk()
            ->viewData('borrowRequests');
        $this->assertSame([$ownBorrowRequest->id], $borrowRequests->getCollection()->modelKeys());

        $this->get(route('employee.bookings.show', $otherBooking))->assertForbidden();
        $this->get(route('employee.borrow-requests.show', $otherBorrowRequest))->assertForbidden();
        $this->get(route('admin.users.show', $otherEmployee))
            ->assertRedirect(route('employee.dashboard'));

        $profile = $this->get(route('profile.edit'))
            ->assertOk()
            ->viewData('user');
        $this->assertTrue($profile->is($employee));

        $export = $this->get(route('profile.export-personal-data'))->assertOk()->json();
        $this->assertSame($employee->id, $export['profile']['id']);
        $this->assertSame(['IMPERSONATION-OWN-BOOKING'], collect($export['bookings'])->pluck('reference_number')->all());
        $this->assertSame(['IMPERSONATION-OWN-BORROW'], collect($export['borrow_requests'])->pluck('request_number')->all());

        $this->get(route('profile.notification-preferences'))
            ->assertOk()
            ->assertSee('Notification Preferences');
        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $employee->id,
            'type' => 'booking',
            'email_enabled' => false,
            'in_app_enabled' => true,
        ]);
        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $otherEmployee->id,
            'email_enabled' => true,
            'in_app_enabled' => false,
        ]);

        $this->post(route('impersonation.stop'))->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $restoredProfile = $this->get(route('profile.edit'))
            ->assertOk()
            ->viewData('user');
        $this->assertTrue($restoredProfile->is($admin));
        $this->assertSame('Original Admin', $restoredProfile->name);
        $this->assertSame('IMPERSONATION-OTHER-BOOKING', $otherBooking->fresh()->reference_number);
        $this->assertSame('IMPERSONATION-OTHER-BORROW', $otherBorrowRequest->fresh()->request_number);
    }

    public function test_regular_employee_still_cannot_open_admin_pages(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }
}
