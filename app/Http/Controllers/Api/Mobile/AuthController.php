<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $key = $this->throttleKey($request);
        $maxAttempts = (int) env('MAX_LOGIN_ATTEMPTS', 5);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again in '.max(1, (int) ceil($seconds / 60)).' minute(s).'],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account is inactive. Contact an administrator.'],
            ]);
        }

        if (! $user->isEmployee()) {
            throw ValidationException::withMessages([
                'email' => ['This app is for employee accounts only.'],
            ]);
        }

        RateLimiter::clear($key);

        $device = $request->input('device_name', 'Android App');

        if ($user->mfa_enabled) {
            $mfaToken = $user->createToken($device, ['mfa-verify'], now()->addMinutes(10));

            return response()->json([
                'mfa_required' => true,
                'mfa_type' => $user->mfa_type ?: 'totp',
                'mfa_token' => $mfaToken->plainTextToken,
            ]);
        }

        $token = $user->createToken($device, ['employee']);

        return $this->tokenResponse($user, $token->plainTextToken);
    }

    public function verifyMfa(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();

        if (! $user || ! $request->user()->tokenCan('mfa-verify')) {
            return response()->json(['message' => 'MFA verification required.'], 403);
        }

        $recoveryCodes = $user->mfa_recovery_codes ?? [];

        if (! $user->verifyTotp($request->code) && ! in_array($request->code, $recoveryCodes, true)) {
            throw ValidationException::withMessages([
                'code' => ['Invalid verification code.'],
            ]);
        }

        $request->user()->currentAccessToken()->delete();

        $device = $request->input('device_name', 'Android App');
        $token = $user->createToken($device, ['employee']);

        return $this->tokenResponse($user, $token->plainTextToken);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    private function tokenResponse(User $user, string $token)
    {
        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar_path' => $user->avatar_path,
            'is_active' => (bool) $user->is_active,
            'mfa_enabled' => (bool) $user->mfa_enabled,
            'role' => $user->isAdmin() ? 'admin' : 'employee',
        ];
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());
    }
}
