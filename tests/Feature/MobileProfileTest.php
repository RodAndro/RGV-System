<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class MobileProfileTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_employee_can_update_name(): void
    {
        $employee = $this->createEmployee(['name' => 'Old Name']);

        Sanctum::actingAs($employee, ['employee']);

        $this->patchJson('/api/v1/mobile/profile', ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonPath('user.name', 'New Name');

        $this->assertSame('New Name', $employee->fresh()->name);
    }

    public function test_email_is_read_only_and_ignored(): void
    {
        $employee = $this->createEmployee(['email' => 'original@example.com']);

        Sanctum::actingAs($employee, ['employee']);

        $this->patchJson('/api/v1/mobile/profile', [
            'name' => 'New Name',
            'email' => 'changed@example.com',
        ])->assertOk();

        $this->assertSame('original@example.com', $employee->fresh()->email);
    }

    public function test_employee_can_change_password(): void
    {
        $employee = $this->createEmployee(['password' => 'old-password']);

        Sanctum::actingAs($employee, ['employee']);

        $this->putJson('/api/v1/mobile/password', [
            'current_password' => 'old-password',
            'new_password' => 'New-Password-123',
            'new_password_confirmation' => 'New-Password-123',
        ])->assertOk();

        $this->assertTrue(Hash::check('New-Password-123', $employee->fresh()->password));
    }

    public function test_change_password_with_wrong_current_password_fails(): void
    {
        $employee = $this->createEmployee(['password' => 'old-password']);

        Sanctum::actingAs($employee, ['employee']);

        $this->putJson('/api/v1/mobile/password', [
            'current_password' => 'wrong-password',
            'new_password' => 'New-Password-123',
            'new_password_confirmation' => 'New-Password-123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('current_password');
    }
}
