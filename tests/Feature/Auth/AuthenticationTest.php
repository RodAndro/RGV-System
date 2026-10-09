<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_login_screen_does_not_query_audit_logs(): void
    {
        $auditQueries = 0;
        DB::listen(function (QueryExecuted $query) use (&$auditQueries): void {
            if (str_contains(strtolower($query->sql), 'audit_logs')) {
                $auditQueries++;
            }
        });

        $this->get('/login')->assertOk();

        $this->assertSame(0, $auditQueries);
    }

    public function test_login_captcha_uses_failed_login_rate_limit(): void
    {
        $key = 'failed-login-ip:127.0.0.1';
        RateLimiter::clear($key);
        RateLimiter::hit($key, 3600);
        RateLimiter::hit($key, 3600);
        RateLimiter::hit($key, 3600);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Please verify you are human.');

        RateLimiter::clear($key);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_failed_login_with_stale_session_user_id_logs_a_guest_audit_event(): void
    {
        $sessionKey = Auth::guard()->getName();

        $this->withSession([$sessionKey => 1])
            ->post('/login', [
                'email' => 'missing-user@example.test',
                'password' => 'wrong-password',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'event' => 'failed login',
        ]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
