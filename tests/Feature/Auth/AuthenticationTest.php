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

    public function test_login_captcha_is_disabled_locally_and_required_in_production(): void
    {
        $key = 'failed-login-ip:127.0.0.1';
        RateLimiter::clear($key);
        RateLimiter::hit($key, 3600);
        RateLimiter::hit($key, 3600);
        RateLimiter::hit($key, 3600);

        $this->app['env'] = 'local';
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('Please verify you are human.');

        $this->app['env'] = 'production';
        $this->get('https://localhost/login')
            ->assertOk()
            ->assertSee('Please verify you are human.');

        $this->post('https://localhost/login', [
            '_token' => session()->token(),
            'email' => 'admin@rgvtech.com',
            'password' => 'Password123',
        ])->assertSessionHasErrors('captcha');

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

    public function test_mfa_verification_page_does_not_expose_the_current_authenticator_code(): void
    {
        $user = User::factory()->create([
            'mfa_enabled' => true,
            'mfa_secret' => 'JBSWY3DPEHPK3PXP',
        ]);
        $currentCode = $user->getCurrentTotpCode();

        $this->withSession(['mfa.pending' => $user->id])
            ->get(route('mfa.verify'))
            ->assertOk()
            ->assertSee('Authenticator Code')
            ->assertSee(route('mfa.verify'))
            ->assertDontSee('Current Code')
            ->assertDontSee('Expires in')
            ->assertDontSee($currentCode);
    }

    public function test_mfa_verification_still_accepts_the_user_entered_authenticator_code(): void
    {
        $user = User::factory()->create([
            'mfa_enabled' => true,
            'mfa_secret' => 'JBSWY3DPEHPK3PXP',
        ]);

        $this->withSession(['mfa.pending' => $user->id, 'mfa.remember' => false])
            ->post(route('mfa.verify'), ['code' => $user->getCurrentTotpCode()])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(session('mfa.verified'));
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
