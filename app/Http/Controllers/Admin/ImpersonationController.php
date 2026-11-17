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
        $request->session()->regenerate();

        return redirect()->route($this->dashboardRoute($user))
            ->with('success', "You are now impersonating {$user->name}.");
    }

    public function stop(Request $request)
    {
        $originalId = session('impersonate.original_id');
        if (!$originalId || !session('impersonate.token')) {
            abort(403, 'No active impersonation session.');
        }

        $admin = User::find($originalId);
        if (!$admin || !$admin->isAdmin()) {
            session()->forget(['impersonate.original_id', 'impersonate.token']);

            return redirect()->route($this->dashboardRoute($request->user()))
                ->with('error', 'The original administrator account is no longer available.');
        }

        session()->forget(['impersonate.original_id', 'impersonate.token']);
        auth()->login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('success', 'You have returned to your account.');
    }

    private function dashboardRoute(User $user): string
    {
        if ($user->hasRole('admin')) {
            return 'admin.dashboard';
        }

        if ($user->hasRole('employee')) {
            return 'employee.dashboard';
        }

        return 'dashboard';
    }
}
