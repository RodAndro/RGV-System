<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MfaController extends Controller
{
    public function show()
    {
        if (!session('mfa.pending')) {
            return redirect()->route('login');
        }

        $user = User::find(session('mfa.pending'));
        if (!$user || !$user->mfa_enabled) {
            session()->forget(['mfa.pending', 'mfa.remember']);
            if ($user) {
                auth()->loginUsingId($user->id, session('mfa.remember', false));
            }
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.mfa-verify', [
            'currentCode' => $user->getCurrentTotpCode(),
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        if (!session('mfa.pending')) {
            return redirect()->route('login');
        }

        $userId = session('mfa.pending');
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login');
        }

        $recoveryCodes = cache('mfa_recovery_' . $userId, $user->mfa_recovery_codes ?? []);

        if ($user->verifyTotp($request->code) || in_array($request->code, $recoveryCodes)) {
            cache()->forget('mfa_recovery_' . $userId);
            session()->forget('mfa.pending');

            auth()->loginUsingId($userId, session('mfa.remember', false));
            session(['mfa.verified' => true]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['code' => 'Invalid verification code. Use an authenticator app like Google Authenticator.']);
    }
}
