<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest as ApiLoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(ApiLoginRequest $request): JsonResponse
    {
        $key = $this->throttleKey($request);
        $maxAttempts = (int) env('MAX_LOGIN_ATTEMPTS', 5);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return $this->error('RATE_LIMITED', 'Too many login attempts. Try again in '
                .max(1, (int) ceil($seconds / 60)).' minute(s).', 429, [], [
                    'Retry-After' => (string) max(1, $seconds),
                ]);
        }

        $user = User::where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            RateLimiter::hit($key, 60);

            return $this->error('UNAUTHENTICATED', 'These credentials do not match our records.', 401);
        }

        if (! $user->is_active) {
            return $this->error('FORBIDDEN', 'This account is inactive. Contact an administrator.', 403);
        }

        RateLimiter::clear($key);

        if ($user->mfa_enabled) {
            $mfaToken = $user->createToken(
                $request->input('device_name', 'SPA'),
                ['mfa-verify'],
                now()->addMinutes(10),
            );

            return response()->json([
                'mfa_required' => true,
                'mfa_type' => $user->mfa_type ?: 'totp',
                'mfa_token' => $mfaToken->plainTextToken,
            ]);
        }

        $token = $user->createToken($request->input('device_name', 'SPA'), ['access']);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->userPayload($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['data' => ['message' => 'Logged out.']]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_active' => (bool) $user->is_active,
            'mfa_enabled' => (bool) $user->mfa_enabled,
            'roles' => $user->getRoleNames()->values()->all(),
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $details
     * @param  array<string, string>  $headers
     */
    private function error(string $code, string $message, int $status, array $details = [], array $headers = []): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $status, $headers);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip());
    }
}
