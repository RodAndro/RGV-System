<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class SupabaseAuthAdminService
{
    public function syncUser(
        User $user,
        ?string $password = null,
        ?bool $isActive = null,
        ?string $role = null,
    ): string {
        if ($user->supabase_auth_id) {
            $linked = $this->findUserById($user->supabase_auth_id);
            if ($linked !== null && $this->belongsToEmployee($linked, $user)) {
                $payload = $this->userPayload($user, $isActive, $role);
                if ($password !== null) {
                    $payload['password'] = $password;
                }

                $this->request(
                    'PUT',
                    'admin/users/'.$user->supabase_auth_id,
                    $payload,
                );

                return $user->supabase_auth_id;
            }
        }

        $existing = $this->findUserByEmail($user->email);
        if ($existing !== null) {
            $this->assertBelongsToEmployee($existing, $user);
            $existingId = (string) $existing['id'];
            $payload = $this->userPayload($user, $isActive, $role);
            if ($password !== null) {
                $payload['password'] = $password;
            }

            $this->request('PUT', 'admin/users/'.urlencode($existingId), $payload);

            return $existingId;
        }

        if ($password === null || $password === '') {
            throw new SupabaseAuthSyncException(
                'Set a password to create this employee account in Supabase Auth.'
            );
        }

        $response = $this->request('POST', 'admin/users', [
            ...$this->userPayload($user, $isActive, $role),
            'password' => $password,
            'email_confirm' => true,
        ]);
        $id = $response['id'] ?? null;

        if (! is_string($id) || $id === '') {
            $existing = $this->findUserByEmail($user->email);
            if ($existing !== null) {
                $this->assertBelongsToEmployee($existing, $user);
                $id = $existing['id'] ?? null;
            }
        }
        if (! is_string($id) || $id === '') {
            throw new SupabaseAuthSyncException(
                'Supabase did not return an authentication account ID.'
            );
        }

        return $id;
    }

    public function deleteUser(string $id): void
    {
        $this->request('DELETE', 'admin/users/'.$id);
    }

    private function userPayload(
        User $user,
        ?bool $isActive = null,
        ?string $role = null,
    ): array {
        return [
            'email' => $user->email,
            'email_confirm' => true,
            'app_metadata' => [
                'employee_id' => $user->id,
                'role' => $role ?? $user->getRoleNames()->first() ?? 'employee',
                'is_active' => $isActive ?? (bool) $user->is_active,
                'mfa_enabled' => (bool) $user->mfa_enabled,
            ],
            'user_metadata' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'avatar_path' => $user->avatar_path,
            ],
        ];
    }

    private function findUserById(string $id): ?array
    {
        try {
            $user = $this->request('GET', 'admin/users/'.urlencode($id));
        } catch (SupabaseAuthSyncException $exception) {
            if ($exception->statusCode === 404) {
                return null;
            }

            throw $exception;
        }

        return isset($user['id']) ? $user : null;
    }

    private function findUserByEmail(string $email): ?array
    {
        $page = 1;
        do {
            $response = $this->request(
                'GET',
                'admin/users?page='.$page.'&per_page=1000',
            );
            $users = $response['users'] ?? [];
            foreach ($users as $candidate) {
                if (
                    isset($candidate['id'], $candidate['email'])
                    && strcasecmp((string) $candidate['email'], $email) === 0
                ) {
                    return $candidate;
                }
            }
            $page++;
        } while (count($users) === 1000);

        return null;
    }

    private function belongsToEmployee(array $authUser, User $employee): bool
    {
        $linkedEmployeeId = $authUser['app_metadata']['employee_id'] ?? null;

        if ($linkedEmployeeId !== null) {
            return (string) $linkedEmployeeId === (string) $employee->getKey();
        }

        return isset($authUser['email'])
            && strcasecmp((string) $authUser['email'], $employee->email) === 0;
    }

    private function assertBelongsToEmployee(array $authUser, User $employee): void
    {
        if (! $this->belongsToEmployee($authUser, $employee)) {
            throw new SupabaseAuthSyncException(
                'The matching Supabase Auth account is linked to a different employee.'
            );
        }
    }

    private function request(string $method, string $path, array $payload = []): array
    {
        $url = rtrim((string) config('services.supabase.url'), '/');
        $publishableKey = (string) config('services.supabase.publishable_key');
        $serverKey = (string) (
            config('services.supabase.secret_key')
            ?: config('services.supabase.service_role_key')
        );
        $isSecretKey = str_starts_with($serverKey, 'sb_secret_');

        if ($url === '' || $serverKey === '' || (! $isSecretKey && $publishableKey === '')) {
            throw new SupabaseAuthSyncException(
                'Supabase Auth synchronization requires SUPABASE_URL and a server-only SUPABASE_SECRET_KEY (or a legacy SUPABASE_SERVICE_ROLE_KEY plus SUPABASE_PUBLISHABLE_KEY).'
            );
        }

        $this->assertDatabaseProjectMatchesAuthProject($url);

        try {
            $headers = $isSecretKey
                ? ['apikey' => $serverKey]
                : [
                    'apikey' => $publishableKey,
                    'Authorization' => 'Bearer '.$serverKey,
                ];
            $request = Http::acceptJson()->withHeaders($headers)->timeout(15);

            $response = match ($method) {
                'DELETE' => $request->delete($url.'/auth/v1/'.$path),
                'GET' => $request->get($url.'/auth/v1/'.$path),
                default => $request->send($method, $url.'/auth/v1/'.$path, [
                    'json' => $payload,
                ]),
            };
        } catch (ConnectionException $exception) {
            throw new SupabaseAuthSyncException(
                'Could not connect to Supabase Auth. The employee account was not saved.',
                previous: $exception,
            );
        }

        if ($response->failed()) {
            $message = $response->json('msg')
                ?? $response->json('message')
                ?? 'Supabase rejected the account update.';

            throw new SupabaseAuthSyncException(
                'Supabase Auth synchronization failed: '.$message,
                statusCode: $response->status(),
            );
        }

        return $response->json() ?? [];
    }

    private function assertDatabaseProjectMatchesAuthProject(string $url): void
    {
        if (config('database.default') !== 'pgsql') {
            return;
        }

        $authHost = parse_url($url, PHP_URL_HOST);
        if (! is_string($authHost)
            || ! preg_match('/^([a-z0-9]+)\.supabase\.co$/i', $authHost, $authMatch)) {
            return;
        }

        $database = config('database.connections.pgsql', []);
        $databaseHost = (string) ($database['host'] ?? '');
        $databaseUsername = (string) ($database['username'] ?? '');
        $databaseProject = null;

        if (preg_match('/^db\.([a-z0-9]+)\.supabase\.co$/i', $databaseHost, $dbMatch)) {
            $databaseProject = $dbMatch[1];
        } elseif (preg_match('/^postgres\.([a-z0-9]+)$/i', $databaseUsername, $dbMatch)) {
            $databaseProject = $dbMatch[1];
        }

        if (
            $databaseProject !== null
            && strcasecmp($authMatch[1], $databaseProject) !== 0
        ) {
            throw new SupabaseAuthSyncException(
                'SUPABASE_URL and the PostgreSQL connection point to different Supabase projects.'
            );
        }
    }
}
