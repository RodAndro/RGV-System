<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function exportPersonalData(Request $request)
    {
        $user = $request->user()->load(['roles', 'borrowRequests.borrowItems.inventory', 'bookings']);

        return response()->json([
            'profile' => $user->only(['id', 'name', 'email', 'phone', 'address', 'is_active', 'created_at']),
            'roles' => $user->roles->pluck('name'),
            'bookings' => $user->bookings,
            'borrow_requests' => $user->borrowRequests,
        ])->header('Content-Disposition', 'attachment; filename="personal-data.json"');
    }

    public function exportOrderHistory(Request $request)
    {
        $bookings = $request->user()->bookings()->latest()->get();

        return Pdf::loadView('pdf.bookings', compact('bookings'))
            ->download('order-history-' . now()->format('Y-m-d') . '.pdf');
    }

    public function enableMfa(Request $request): RedirectResponse
    {
        $request->user()->enableMfa();

        return Redirect::route('profile.edit')->with('mfa_status', 'Two-factor authentication has been enabled. Save your recovery codes below.');
    }

    public function disableMfa(Request $request): RedirectResponse
    {
        $request->user()->disableMfa();

        return Redirect::route('profile.edit')->with('mfa_status', 'Two-factor authentication has been disabled.');
    }

    public function totpCode(Request $request)
    {
        $code = $request->user()->getCurrentTotpCode();
        return response()->json(['code' => $code]);
    }

    public function notificationPreferences(Request $request)
    {
        $user = $request->user();
        $types = ['booking', 'borrow_request', 'inventory', 'system', 'user'];

        $preferences = [];
        foreach ($types as $type) {
            $pref = $user->notificationPreferences()->firstOrCreate(
                ['type' => $type],
                ['email_enabled' => true, 'in_app_enabled' => true]
            );
            $preferences[$type] = $pref;
        }

        return view('profile.notification-preferences', compact('preferences'));
    }

    public function updateNotificationPreferences(Request $request): RedirectResponse
    {
        $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.*.type' => ['required', 'string'],
            'preferences.*.email_enabled' => ['sometimes', 'boolean'],
            'preferences.*.in_app_enabled' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();

        foreach ($request->preferences as $pref) {
            $user->notificationPreferences()->updateOrCreate(
                ['type' => $pref['type']],
                [
                    'email_enabled' => $pref['email_enabled'] ?? false,
                    'in_app_enabled' => $pref['in_app_enabled'] ?? false,
                ]
            );
        }

        return Redirect::route('profile.edit')->with('success', 'Notification preferences updated.');
    }
}
