<?php

namespace Tests\Feature;

use App\Jobs\ProcessInventoryImport;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\NotificationPreference;
use App\Notifications\BookingAssigned;
use App\Notifications\LowStockAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class PlatformFeaturesTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

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
            ->assertDontSee('<?php echo e($user->name); ?>');
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

    public function test_admin_created_employee_is_provisioned_in_supabase_auth(): void
    {
        config([
            'services.supabase.url' => 'https://project.supabase.co',
            'services.supabase.secret_key' => 'test-server-secret',
        ]);
        Http::fake([
            'https://project.supabase.co/auth/v1/admin/users' => Http::response([
                'id' => '123e4567-e89b-12d3-a456-426614174000',
            ], 201),
        ]);

        $admin = $this->createAdmin();
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Native Employee',
            'email' => 'native.employee@example.test',
            'phone' => '+639123456789',
            'address' => 'Davao City',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'role' => 'employee',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'native.employee@example.test',
            'supabase_auth_id' => '123e4567-e89b-12d3-a456-426614174000',
        ]);
        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && $request->url() === 'https://project.supabase.co/auth/v1/admin/users'
            && $request->hasHeader('Authorization', 'Bearer test-server-secret')
            && $request->data()['email_confirm'] === true
            && $request->data()['user_metadata']['name'] === 'Native Employee');
    }

    public function test_admin_employee_edits_are_synced_to_the_linked_supabase_auth_account(): void
    {
        config([
            'services.supabase.url' => 'https://project.supabase.co',
            'services.supabase.secret_key' => 'test-server-secret',
        ]);
        Http::fake([
            'https://project.supabase.co/auth/v1/admin/users/*' => Http::response([
                'id' => '123e4567-e89b-12d3-a456-426614174000',
            ]),
        ]);

        $admin = $this->createAdmin();
        $employee = $this->createEmployee(['email' => 'before@example.test']);
        $employee->forceFill(['supabase_auth_id' => '123e4567-e89b-12d3-a456-426614174000'])->save();

        $this->actingAs($admin)->put(route('admin.users.update', $employee), [
            'name' => 'Updated Employee',
            'email' => 'after@example.test',
            'phone' => '+639123456789',
            'address' => 'Davao City',
            'password' => 'UpdatedPassword123!',
            'password_confirmation' => 'UpdatedPassword123!',
            'role' => 'employee',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'name' => 'Updated Employee',
            'email' => 'after@example.test',
            'supabase_auth_id' => '123e4567-e89b-12d3-a456-426614174000',
        ]);
        Http::assertSent(fn (Request $request) => $request->method() === 'PUT'
            && $request->url() === 'https://project.supabase.co/auth/v1/admin/users/123e4567-e89b-12d3-a456-426614174000'
            && $request->data()['email'] === 'after@example.test'
            && $request->data()['password'] === 'UpdatedPassword123!'
            && $request->data()['user_metadata']['name'] === 'Updated Employee');
    }

    public function test_admin_employee_status_changes_are_synced_to_supabase_auth_metadata(): void
    {
        config([
            'services.supabase.url' => 'https://project.supabase.co',
            'services.supabase.secret_key' => 'test-server-secret',
        ]);
        Http::fake([
            'https://project.supabase.co/auth/v1/admin/users/*' => Http::response([
                'id' => '123e4567-e89b-12d3-a456-426614174000',
            ]),
        ]);

        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $employee->forceFill(['supabase_auth_id' => '123e4567-e89b-12d3-a456-426614174000'])->save();

        $this->actingAs($admin)
            ->post(route('admin.users.toggle-status', $employee))
            ->assertRedirect();

        $this->assertFalse($employee->fresh()->is_active);
        Http::assertSent(fn (Request $request) => $request->method() === 'PUT'
            && $request->data()['user_metadata']['is_active'] === false);
    }

    public function test_deleting_employee_also_deletes_the_linked_supabase_auth_account(): void
    {
        config([
            'services.supabase.url' => 'https://project.supabase.co',
            'services.supabase.secret_key' => 'test-server-secret',
        ]);
        Http::fake([
            'https://project.supabase.co/auth/v1/admin/users/*' => Http::response([], 204),
        ]);

        $admin = $this->createAdmin(['password' => 'AdminPassword123!']);
        $employee = $this->createEmployee(['is_active' => false]);
        $authUserId = '123e4567-e89b-12d3-a456-426614174000';
        $employee->forceFill(['supabase_auth_id' => $authUserId])->save();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $employee), ['password' => 'AdminPassword123!'])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $employee->id]);
        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE'
            && $request->url() === "https://project.supabase.co/auth/v1/admin/users/{$authUserId}"
            && $request->hasHeader('Authorization', 'Bearer test-server-secret'));
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
