<?php

namespace Tests\Feature;

use App\Jobs\ProcessInventoryImport;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\BookingAssigned;
use App\Notifications\LowStockAlert;
use App\Services\NotificationSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class PlatformFeaturesTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    /**
     * Tests in this class create and remove roles. RefreshDatabase rolls the
     * rows back, so Spatie's cached permission map must be cleared between
     * tests or a later test can read a role that no longer exists.
     */
    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        parent::tearDown();
    }

    public function test_inventory_import_is_logged_and_queued(): void
    {
        Queue::fake();
        Storage::fake('local');

        $user = $this->createAdmin();
        $file = UploadedFile::fake()->createWithContent(
            'inventory.csv',
            "item_code,name,description,category,quantity,unit_cost\nRGV-001,Hammer,Steel hammer,Tools,10,12.50\n"
        );

        $this->actingAs($user)
            ->post(route('admin.import-export.inventory.import'), [
                'file' => $file,
                'duplicate_strategy' => 'skip',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('import_logs', [
            'type' => 'inventory',
            'status' => 'queued',
            'duplicate_strategy' => 'skip',
        ]);

        Queue::assertPushed(ProcessInventoryImport::class);
    }

    public function test_low_stock_alert_only_fires_when_inventory_crosses_the_threshold(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();
        $inventory = $this->createInventory([
            'quantity' => 8,
            'low_stock_threshold' => 5,
        ]);
        $data = [
            'item_code' => $inventory->item_code,
            'name' => $inventory->name,
            'category_id' => $inventory->category_id,
            'quantity' => 5,
            'unit' => $inventory->unit,
            'unit_cost' => $inventory->unit_cost,
            'status' => $inventory->status,
            'condition' => $inventory->condition,
            'location' => $inventory->location,
            'low_stock_threshold' => 5,
            'date_added' => $inventory->date_added->format('Y-m-d'),
        ];

        $this->actingAs($admin)
            ->put(route('admin.inventories.update', $inventory), $data)
            ->assertRedirect();
        Notification::assertSentToTimes($admin, LowStockAlert::class, 1);

        $data['quantity'] = 4;
        $this->put(route('admin.inventories.update', $inventory), $data)
            ->assertRedirect();
        Notification::assertSentToTimes($admin, LowStockAlert::class, 1);
    }

    public function test_reassigning_the_same_employee_does_not_send_an_assignment_notice(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $booking = Booking::factory()->create(['employee_id' => $employee->id]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.assign', $booking), ['employee_id' => $employee->id])
            ->assertRedirect();

        Notification::assertNotSentTo($employee, BookingAssigned::class);
    }

    public function test_work_request_rejection_modal_posts_to_the_redirecting_reject_route(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();
        $booking = Booking::factory()->create(['status' => 'pending']);
        $actionTemplate = route('admin.bookings.reject', ['booking' => '__BOOKING_ID__']);

        $this->actingAs($admin)
            ->get(route('admin.bookings.index'))
            ->assertOk()
            ->assertSee($actionTemplate);

        $this->from(route('admin.bookings.index'))
            ->post(route('admin.bookings.reject', $booking), ['remarks' => 'Unavailable at requested time.'])
            ->assertRedirect(route('admin.bookings.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'rejected',
            'remarks' => 'Unavailable at requested time.',
        ]);
    }

    public function test_approved_work_request_cannot_be_rejected(): void
    {
        $admin = $this->createAdmin();
        $booking = Booking::factory()->create(['status' => 'approved']);

        $this->actingAs($admin)
            ->from(route('admin.bookings.index'))
            ->post(route('admin.bookings.reject', $booking), ['remarks' => 'No longer needed.'])
            ->assertRedirect(route('admin.bookings.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'approved',
        ]);
    }

    public function test_rejected_work_request_can_be_moved_to_trash_and_restored(): void
    {
        $admin = $this->createAdmin();
        $booking = Booking::factory()->create(['status' => 'rejected']);

        $this->actingAs($admin)
            ->get(route('admin.bookings.index'))
            ->assertOk()
            ->assertSee(route('admin.bookings.destroy', $booking));

        $this->actingAs($admin)
            ->delete(route('admin.bookings.destroy', $booking))
            ->assertRedirect(route('admin.bookings.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('bookings', ['id' => $booking->id]);
        $this->get(route('admin.trash.index', ['type' => 'booking']))
            ->assertOk()
            ->assertSee($booking->reference_number);

        $this->post(route('admin.trash.restore', ['type' => 'booking', 'id' => $booking->id]))
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted('bookings', ['id' => $booking->id]);
    }

    public function test_database_notifications_persist_in_the_bell_inbox_schema(): void
    {
        $admin = $this->createAdmin();
        $inventory = $this->createInventory([
            'quantity' => 10,
            'low_stock_threshold' => 2,
        ]);
        NotificationPreference::create([
            'user_id' => $admin->id,
            'type' => 'inventory',
            'email_enabled' => false,
            'in_app_enabled' => true,
        ]);

        $admin->notify(new LowStockAlert($inventory));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type' => 'inventory',
            'title' => 'Low Stock Alert',
            'is_read' => false,
        ]);
    }

    public function test_notification_preferences_are_account_scoped_and_honor_global_email_toggle(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $inventory = $this->createInventory(['quantity' => 1, 'low_stock_threshold' => 2]);
        $notification = new LowStockAlert($inventory);

        NotificationPreference::create([
            'user_id' => $admin->id,
            'type' => 'inventory',
            'email_enabled' => true,
            'in_app_enabled' => false,
        ]);
        NotificationPreference::create([
            'user_id' => $employee->id,
            'type' => 'inventory',
            'email_enabled' => false,
            'in_app_enabled' => true,
        ]);

        $originalValue = getenv('NOTIFICATION_EMAIL_ENABLED');
        $originalEnv = $_ENV['NOTIFICATION_EMAIL_ENABLED'] ?? null;
        $originalServer = $_SERVER['NOTIFICATION_EMAIL_ENABLED'] ?? null;
        $setGlobalEmail = static function (string $value): void {
            putenv('NOTIFICATION_EMAIL_ENABLED='.$value);
            $_ENV['NOTIFICATION_EMAIL_ENABLED'] = $value;
            $_SERVER['NOTIFICATION_EMAIL_ENABLED'] = $value;
        };

        try {
            $setGlobalEmail('true');
            $this->assertTrue(NotificationSettings::emailEnabled());
            $this->assertSame(['mail'], $notification->via($admin));
            $this->assertSame(['database'], $notification->via($employee));

            $this->actingAs($admin);
            $settings = $this->get(route('admin.settings.index'))
                ->assertOk()
                ->viewData('settings');
            $this->assertTrue($settings['notifications']['email_enabled']);

            $this->actingAs($employee)
                ->put(route('profile.notification-preferences.update'), [
                    'preferences' => [[
                        'type' => 'inventory',
                        'email_enabled' => true,
                        'in_app_enabled' => false,
                    ]],
                ])
                ->assertRedirect(route('profile.edit'));

            $this->assertDatabaseHas('notification_preferences', [
                'user_id' => $employee->id,
                'type' => 'inventory',
                'email_enabled' => true,
                'in_app_enabled' => false,
            ]);
            $this->assertDatabaseHas('notification_preferences', [
                'user_id' => $admin->id,
                'type' => 'inventory',
                'email_enabled' => true,
                'in_app_enabled' => false,
            ]);

            $setGlobalEmail('false');
            $this->assertFalse(NotificationSettings::emailEnabled());
            $this->assertSame([], $notification->via($admin));
            $this->assertSame([], $notification->via($employee));

            $this->actingAs($admin);
            $settings = $this->get(route('admin.settings.index'))
                ->assertOk()
                ->viewData('settings');
            $this->assertFalse($settings['notifications']['email_enabled']);
        } finally {
            $originalValue === false
                ? putenv('NOTIFICATION_EMAIL_ENABLED')
                : putenv('NOTIFICATION_EMAIL_ENABLED='.$originalValue);

            if ($originalEnv === null) {
                unset($_ENV['NOTIFICATION_EMAIL_ENABLED']);
            } else {
                $_ENV['NOTIFICATION_EMAIL_ENABLED'] = $originalEnv;
            }

            if ($originalServer === null) {
                unset($_SERVER['NOTIFICATION_EMAIL_ENABLED']);
            } else {
                $_SERVER['NOTIFICATION_EMAIL_ENABLED'] = $originalServer;
            }
        }
    }

    public function test_audit_logs_have_valid_checksums(): void
    {
        $log = AuditLog::create([
            'event' => 'test event',
            'old_values' => ['status' => 'old'],
            'new_values' => ['status' => 'new'],
            'ip_address' => '127.0.0.1',
            'url' => 'http://localhost/test',
        ]);

        $this->assertTrue($log->fresh()->isChecksumValid());
    }

    public function test_inventory_return_and_cleanup_migrations_match_the_current_schema(): void
    {
        $this->assertTrue(Schema::hasColumn('inventories', 'qr_token'));
        $this->assertTrue(Schema::hasColumn('borrow_requests', 'return_location'));
        $this->assertFalse(Schema::hasTable('suppliers'));
        $this->assertFalse(Schema::hasColumn('inventories', 'supplier_id'));
        $this->assertFalse(Schema::hasColumn('borrow_requests', 'penalty_notes'));

        $qrTokenIndex = collect(Schema::getIndexes('inventories'))
            ->first(fn (array $index) => $index['columns'] === ['qr_token']);

        $this->assertNotNull($qrTokenIndex);
        $this->assertTrue($qrTokenIndex['unique']);
    }

    public function test_admin_user_edit_page_renders_the_name_in_its_header(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee(['name' => 'Edit Form Employee']);

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $employee))
            ->assertOk()
            ->assertSee('Edit User: Edit Form Employee')
            ->assertSee('::-ms-reveal', false)
            ->assertSee('::-webkit-credentials-auto-fill-button', false)
            ->assertSee('autocomplete="new-password"', false)
            ->assertDontSee('<?php echo e($user->name); ?>');
    }

    public function test_add_user_password_fields_have_one_conditional_visibility_toggle(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.users.create'));
        $response->assertOk();

        $html = $response->getContent();

        // Exactly one toggle and one icon per password field.
        $this->assertSame(1, substr_count($html, 'data-password-toggle="password"'));
        $this->assertSame(1, substr_count($html, 'data-password-toggle="password_confirmation"'));
        $this->assertSame(1, substr_count($html, 'id="eye-icon" class='));
        $this->assertSame(1, substr_count($html, 'id="eye-icon-confirm" class='));
        $this->assertStringNotContainsString('class="fas fa-eye-slash"', $html);

        // The toggle starts hidden and is revealed by input length in script.
        $this->assertStringContainsString(
            'absolute inset-y-0 right-0 hidden items-center',
            $html
        );
        $this->assertStringContainsString('input.value.length > 0', $html);

        // Browser-native reveal/autofill controls are suppressed.
        $this->assertStringContainsString('::-ms-reveal', $html);
        $this->assertStringContainsString('::-webkit-credentials-auto-fill-button', $html);
    }

    public function test_user_management_only_assigns_admin_and_employee_roles(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        Role::create(['name' => 'manager', 'guard_name' => 'web']);

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertDontSee('manager');

        $this->post(route('admin.users.store'), [
            'name' => 'Unsupported Role User',
            'email' => 'unsupported-role@example.test',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'role' => 'manager',
        ])->assertSessionHasErrors('role');

        $this->put(route('admin.users.update', $employee), [
            'name' => $employee->name,
            'email' => $employee->email,
            'role' => 'manager',
        ])->assertSessionHasErrors('role');

        $this->assertTrue($employee->fresh()->hasRole('employee'));
    }

    public function test_last_admin_cannot_be_demoted_or_deactivated(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'employee',
            ])
            ->assertSessionHas('error', 'The last administrator cannot be removed or deactivated.');

        $this->assertTrue($admin->fresh()->hasRole('admin'));

        $this->post(route('admin.users.toggle-status', $admin))
            ->assertSessionHas('error', 'You cannot deactivate the last active administrator account.');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_created_employee_can_log_into_the_mobile_app(): void
    {
        Http::fake();

        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Mobile Employee',
            'email' => 'mobile.employee@example.test',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'role' => 'employee',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $employee = User::where('email', 'mobile.employee@example.test')->firstOrFail();

        // The local account is complete on its own; no external identity
        // provider is contacted during provisioning.
        $this->assertTrue($employee->hasRole('employee'));
        $this->assertTrue(Hash::check('SecurePassword123!', $employee->password));
        Http::assertNothingSent();

        // And the employee can sign into the mobile API with those credentials.
        $this->postJson('/api/v1/mobile/auth/login', [
            'email' => 'mobile.employee@example.test',
            'password' => 'SecurePassword123!',
        ])
            ->assertOk()
            ->assertJsonPath('user.email', 'mobile.employee@example.test')
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);
    }

    public function test_admin_creation_command_creates_required_roles(): void
    {
        $this->artisan('app:create-admin admin-command@example.test')
            ->expectsQuestion('New password', 'SecurePassword123!')
            ->expectsQuestion('Confirm new password', 'SecurePassword123!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('roles', ['name' => 'admin', 'guard_name' => 'web']);
        $this->assertDatabaseHas('roles', ['name' => 'employee', 'guard_name' => 'web']);
    }

    public function test_admin_employee_edits_update_the_local_account_and_revoke_tokens_after_password_change(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee(['email' => 'before@example.test']);
        $token = $employee->createToken('test')->accessToken;

        $this->actingAs($admin)->put(route('admin.users.update', $employee), [
            'name' => 'Updated Employee',
            'email' => 'after@example.test',
            'phone' => '+639123456789',
            'address' => 'Davao City',
            'password' => 'UpdatedPassword123!',
            'password_confirmation' => 'UpdatedPassword123!',
            'role' => 'employee',
        ])->assertRedirect(route('admin.users.show', $employee))
            ->assertSessionHas(
                'success',
                "Credentials for 'Updated Employee' have been updated successfully.",
            );

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'name' => 'Updated Employee',
            'email' => 'after@example.test',
        ]);
        $this->get(route('admin.users.show', $employee))
            ->assertOk()
            ->assertSee("Credentials for 'Updated Employee' have been updated successfully.");
        $this->assertTrue(Hash::check('UpdatedPassword123!', $employee->fresh()->password));
        // A password change must invalidate every issued bearer token.
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->id]);
    }

    public function test_admin_employee_deactivation_revokes_mobile_tokens(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $token = $employee->createToken('test')->accessToken;

        $this->actingAs($admin)
            ->post(route('admin.users.toggle-status', $employee), ['password' => 'password'])
            ->assertRedirect();

        $this->assertFalse($employee->fresh()->is_active);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->id]);
    }

    public function test_deleting_employee_removes_the_local_account_without_external_auth(): void
    {
        $admin = $this->createAdmin(['password' => 'AdminPassword123!']);
        $employee = $this->createEmployee(['is_active' => false]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $employee), ['password' => 'AdminPassword123!'])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $employee->id]);
    }

    public function test_api_rate_limit_returns_custom_429_response(): void
    {
        $category = InventoryCategory::create([
            'name' => 'Tools',
            'slug' => 'tools',
            'is_active' => true,
        ]);

        Inventory::factory()->create(['category_id' => $category->id]);

        for ($i = 0; $i < 5; $i++) {
            $this->getJson('/api/inventories')->assertOk();
        }

        $this->getJson('/api/inventories')
            ->assertStatus(429)
            ->assertHeader('Retry-After')
            ->assertJson([
                'message' => 'Too many requests.',
                'tier' => 'public',
            ]);
    }
}
