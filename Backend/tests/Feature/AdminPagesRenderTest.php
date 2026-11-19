<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

/**
 * Every admin page must render without a server error.
 *
 * These are cheap smoke checks that catch template-level breakage (a missing
 * array key, a removed variable) which unit tests on controllers do not see.
 */
class AdminPagesRenderTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->ensureRoles();
    }

    public function test_every_admin_page_renders(): void
    {
        $admin = $this->createAdmin();

        $pages = [
            'admin.dashboard',
            'admin.inventories.index',
            'admin.inventories.create',
            'admin.bookings.index',
            'admin.borrow-requests.index',
            'admin.users.index',
            'admin.users.create',
            'admin.reports.index',
            'admin.reports.bookings',
            'admin.reports.inventory',
            'admin.reports.users',
            'admin.notifications.index',
            'admin.audit.index',
            'admin.backups.index',
            'admin.settings.index',
            'admin.trash.index',
            'admin.import-export.index',
            'admin.system-health',
        ];

        foreach ($pages as $route) {
            $this->actingAs($admin)
                ->get(route($route))
                ->assertSuccessful();
        }
    }

    public function test_every_employee_page_renders(): void
    {
        $employee = $this->createEmployee();

        $pages = [
            'employee.dashboard',
            'employee.inventories.index',
            'employee.inventories.low-stock',
            'employee.borrow-requests.index',
            'employee.borrow-requests.create',
            'employee.bookings.index',
            'employee.notifications.index',
            'profile.edit',
        ];

        foreach ($pages as $route) {
            $this->actingAs($employee)
                ->get(route($route))
                ->assertSuccessful();
        }
    }

    /**
     * The modal component maps maxWidth names to Tailwind classes. A name with
     * no entry used to raise "Undefined array key" and return a 500, so keep the
     * sizes it advertises in sync with its callers.
     */
    public function test_modal_component_supports_every_advertised_width(): void
    {
        foreach (['sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl'] as $width) {
            $html = $this->blade(
                '<x-modal name="test-'.$width.'" maxWidth="'.$width.'">body</x-modal>'
            );

            $this->assertStringContainsString('max-w-'.$width, (string) $html);
        }
    }
}
