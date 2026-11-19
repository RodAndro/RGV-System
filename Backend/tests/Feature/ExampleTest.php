<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_public_homepage_does_not_write_page_visit_audit_by_default(): void
    {
        $auditWrites = 0;

        DB::listen(function (QueryExecuted $query) use (&$auditWrites): void {
            if (str_starts_with(strtolower(ltrim($query->sql)), 'insert into "audit_logs"')) {
                $auditWrites++;
            }
        });

        $this->get('/')->assertOk();

        $this->assertSame(0, $auditWrites);
    }

    public function test_notification_bell_renders_without_querying_notifications(): void
    {
        $this->actingAs(User::factory()->create());
        $notificationQueries = 0;

        DB::listen(function (QueryExecuted $query) use (&$notificationQueries): void {
            if (str_contains(strtolower($query->sql), 'notifications')) {
                $notificationQueries++;
            }
        });

        $html = view('components.notification-bell')->render();

        $this->assertStringContainsString('unreadCount: 0', $html);
        $this->assertSame(0, $notificationQueries);
    }
}
