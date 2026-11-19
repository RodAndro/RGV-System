<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class V1AuthTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->ensureRoles();
    }

    public function test_login_returns_a_bearer_token_for_valid_credentials(): void
    {
        $employee = $this->createEmployee(['email' => 'employee@example.test']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $employee->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'name', 'email', 'roles']])
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.roles.0', 'employee');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $employee = $this->createEmployee(['email' => 'employee@example.test']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $employee->email,
            'password' => 'wrong-password',
        ])
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED')
            ->assertJsonStructure(['error' => ['code', 'message', 'details']]);
    }

    public function test_login_validation_uses_the_documented_error_envelope(): void
    {
        $this->postJson('/api/v1/auth/login', ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['error' => ['code', 'message', 'details']]);
    }

    public function test_inactive_account_cannot_log_in(): void
    {
        $employee = $this->createEmployee([
            'email' => 'inactive@example.test',
            'is_active' => false,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $employee->email,
            'password' => 'password',
        ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_me_returns_the_authenticated_user(): void
    {
        $admin = $this->createAdmin(['email' => 'admin@example.test']);
        Sanctum::actingAs($admin, ['access']);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@example.test')
            ->assertJsonPath('data.roles.0', 'admin');
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $employee = $this->createEmployee();
        $token = $employee->createToken('test', ['access'])->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
