<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class MobileAuthTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_employee_can_login_and_receive_a_token(): void
    {
        $employee = $this->createEmployee(['password' => 'password']);

        $response = $this->postJson('/api/v1/mobile/auth/login', [
            'email' => $employee->email,
            'password' => 'password',
            'device_name' => 'Pixel 9',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user'])
            ->assertJsonPath('user.email', $employee->email)
            ->assertJsonPath('user.role', 'employee');

        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_with_invalid_credentials_fails(): void
    {
        $employee = $this->createEmployee(['password' => 'password']);

        $response = $this->postJson('/api/v1/mobile/auth/login', [
            'email' => $employee->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_inactive_account_is_rejected(): void
    {
        $employee = $this->createEmployee(['password' => 'password', 'is_active' => false]);

        $response = $this->postJson('/api/v1/mobile/auth/login', [
            'email' => $employee->email,
            'password' => 'password',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_admin_account_is_rejected_from_mobile(): void
    {
        $admin = $this->createAdmin(['password' => 'password']);

        $response = $this->postJson('/api/v1/mobile/auth/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_mfa_login_returns_pending_then_verify_issues_token(): void
    {
        $employee = $this->createEmployee(['password' => 'password']);
        $employee->enableMfa('totp');

        $login = $this->postJson('/api/v1/mobile/auth/login', [
            'email' => $employee->email,
            'password' => 'password',
        ]);

        $login->assertOk()
            ->assertJsonPath('mfa_required', true)
            ->assertJsonStructure(['mfa_token']);

        $code = $employee->fresh()->getCurrentTotpCode();

        $verify = $this->withToken($login->json('mfa_token'))
            ->postJson('/api/v1/mobile/auth/mfa/verify', ['code' => $code]);

        $verify->assertOk()
            ->assertJsonStructure(['token', 'user'])
            ->assertJsonPath('user.email', $employee->email);
    }

    public function test_mfa_verify_with_invalid_code_fails(): void
    {
        $employee = $this->createEmployee(['password' => 'password']);
        $employee->enableMfa('totp');

        $login = $this->postJson('/api/v1/mobile/auth/login', [
            'email' => $employee->email,
            'password' => 'password',
        ]);

        $verify = $this->withToken($login->json('mfa_token'))
            ->postJson('/api/v1/mobile/auth/mfa/verify', ['code' => '000000']);

        $verify->assertStatus(422)->assertJsonValidationErrors('code');
    }

    public function test_logout_revokes_token(): void
    {
        $employee = $this->createEmployee();
        $token = $employee->createToken('test', ['employee'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/mobile/auth/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_me_returns_current_employee(): void
    {
        $employee = $this->createEmployee();

        Sanctum::actingAs($employee, ['employee']);

        $this->getJson('/api/v1/mobile/me')
            ->assertOk()
            ->assertJsonPath('user.id', $employee->id)
            ->assertJsonPath('user.email', $employee->email);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/v1/mobile/me')->assertStatus(401);
    }

    public function test_inactive_employee_token_is_rejected(): void
    {
        $employee = $this->createEmployee(['is_active' => false]);

        Sanctum::actingAs($employee, ['employee']);

        $this->getJson('/api/v1/mobile/me')->assertStatus(403);
    }
}
