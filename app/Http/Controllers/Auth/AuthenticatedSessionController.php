<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        $bruteForceDetected = AuditLog::where('event', 'failed login')
            ->where('ip_address', request()->ip())
            ->where('created_at', '>=', now()->subHour())
            ->count() >= 3;

        $captchaA = $captchaB = null;

        if ($bruteForceDetected) {
            $captchaA = rand(1, 10);
            $captchaB = rand(1, 10);
            session(['captcha_answer' => $captchaA + $captchaB, 'captcha_required' => true]);
        } else {
            session()->forget(['captcha_answer', 'captcha_required']);
        }

        return view('auth.login', compact('bruteForceDetected', 'captchaA', 'captchaB'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->mfa_enabled) {
            Auth::guard('web')->logout();
            cache(['mfa_recovery_' . $user->id => $user->mfa_recovery_codes ?? []], now()->addMinutes(10));
            session(['mfa.pending' => $user->id]);
            session(['mfa.remember' => $request->boolean('remember')]);
            return redirect()->route('mfa.verify');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
