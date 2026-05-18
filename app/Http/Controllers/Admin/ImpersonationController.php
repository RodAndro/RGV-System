<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function start(Request $request, User $user)
    {
        $admin = $request->user();

        if ($user->id === $admin->id) {
            return back()->with('error', 'You cannot impersonate yourself.');
        }

        if ($user->mfa_enabled) {
            return back()->with('error', 'Cannot impersonate this user — they have two-factor authentication enabled.');
        }

        session(['impersonate.original_id' => $admin->id]);
        session(['impersonate.token' => bin2hex(random_bytes(16))]);

        auth()->login($user);

        return redirect()->route('dashboard')->with('success', "You are now impersonating {$user->name}.");
    }

    public function stop(Request $request)
    {
        $originalId = session('impersonate.original_id');
        if (!$originalId) {
            return redirect()->route('admin.dashboard');
        }

        $admin = User::find($originalId);
        if (!$admin) {
            return redirect()->route('login');
        }

        auth()->login($admin);
        session()->forget(['impersonate.original_id', 'impersonate.token']);

        return redirect()->route('admin.dashboard')->with('success', 'You have returned to your account.');
    }
}
