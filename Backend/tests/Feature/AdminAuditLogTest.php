<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class AdminAuditLogTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_audit_logs_are_cleared_after_confirming_with_the_correct_password(): void
    {
        $admin = $this->createAdmin();
        $this->createAuditLog('first test event');
        $this->createAuditLog('second test event');
        $auditUrl = route('admin.audit.index');

        $this->actingAs($admin)
            ->from($auditUrl)
            ->post(route('admin.audit.clear-all'), ['password' => 'password'])
            ->assertRedirect($auditUrl)
            ->assertSessionHas('success', 'All audit logs have been cleared.');

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_audit_logs_are_not_cleared_with_an_incorrect_password(): void
    {
        $admin = $this->createAdmin();
        $this->createAuditLog('test event');
        $auditUrl = route('admin.audit.index');

        $this->actingAs($admin)
            ->from($auditUrl)
            ->post(route('admin.audit.clear-all'), ['password' => 'incorrect-password'])
            ->assertRedirect($auditUrl)
            ->assertSessionHasErrors('password');

        $this->assertDatabaseHas('audit_logs', ['event' => 'test event']);
        $this->get($auditUrl)
            ->assertOk()
            ->assertSee('The password is incorrect.')
            ->assertSee('show: true', false)
            ->assertSee('name="password"', false);
    }

    private function createAuditLog(string $event): AuditLog
    {
        return AuditLog::create([
            'event' => $event,
            'ip_address' => '127.0.0.1',
            'url' => 'http://localhost/test',
        ]);
    }
}
