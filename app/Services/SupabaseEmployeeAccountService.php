<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SupabaseEmployeeAccountService
{
    public function sync(array $employee, ?string $authUserId = null, ?string $password = null, array $lookupEmails = []): string
    {
        $url = rtrim((string) config('services.supabase.url'), '/');
        $secretKey = (string) config('services.supabase.secret_key');

        if ($url === '' || $secretKey === '') {
            throw new RuntimeException('Supabase Auth sync is not configured. Set SUPABASE_URL and the server-only SUPABASE_SECRET_KEY.');
        }

        $payload = [
            'email' => $employee['email'],
            'email_confirm' => true,
            'user_metadata' => [
                'name' => $employee['name'],
                'phone' => $employee['phone'] ?? null,
                'address' => $employee['address'] ?? null,
                'role' => $employee['role'],
                'is_active' => (bool) ($employee['is_active'] ?? true),
            ],
        ];

        if ($password !== null && $password !== '') {
            $payload['password'] = $password;
        }

        try {
            if ($authUserId !== null) {
                return $this->updateUser($authUserId, $payload);
            }

            if ($lookupEmails !== []) {
                $existingUser = $this->findUserByEmail($lookupEmails, $url, $secretKey);
                if ($existingUser !== null) {
                    return $this->updateUser($existingUser['id'], $payload);
                }
            }

            if ($password === null || $password === '') {
                throw new RuntimeException('No matching Supabase account exists. Set a new password to create this employee\'s mobile login.');
            }

            $response = $this->client($secretKey)->post("{$url}/auth/v1/admin/users", $payload);
            if ($response->successful()) {
                return $this->responseUserId($response);
            }

            $existingUser = $this->findUserByEmail([$employee['email']], $url, $secretKey);
            if ($existingUser !== null) {
                return $this->updateUser($existingUser['id'], $payload);
            }

            throw new RuntimeException("Supabase rejected the employee account request (HTTP {$response->status()}).");
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Could not reach Supabase Auth. Check the Supabase URL and server configuration.', 0, $exception);
        }
    }

    public function delete(string $authUserId): void
    {
        $url = rtrim((string) config('services.supabase.url'), '/');
        $secretKey = (string) config('services.supabase.secret_key');

        if ($url === '' || $secretKey === '') {
            throw new RuntimeException('Supabase Auth sync is not configured. Set SUPABASE_URL and the server-only SUPABASE_SECRET_KEY.');
        }

        try {
            $response = $this->client($secretKey)->delete(
                "{$url}/auth/v1/admin/users/".urlencode($authUserId),
            );

            if (! $response->successful() && $response->status() !== 404) {
                throw new RuntimeException("Supabase could not delete the employee account (HTTP {$response->status()}).");
            }
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Could not reach Supabase Auth. Check the Supabase URL and server configuration.', 0, $exception);
        }
    }

    private function updateUser(string $userId, array $payload): string
    {
        $url = rtrim((string) config('services.supabase.url'), '/');
        $response = $this->client((string) config('services.supabase.secret_key'))
            ->put("{$url}/auth/v1/admin/users/{$userId}", $payload);

        if (! $response->successful()) {
            throw new RuntimeException("Supabase could not update the employee account (HTTP {$response->status()}).");
        }

        return $this->responseUserId($response, $userId);
    }

    private function findUserByEmail(array $emails, string $url, string $secretKey): ?array
    {
        $emails = array_fill_keys(array_map('strtolower', $emails), true);
        $page = 1;

        do {
            $response = $this->client($secretKey)->get("{$url}/auth/v1/admin/users", [
                'page' => $page,
                'per_page' => 1000,
            ]);

            if (! $response->successful()) {
                throw new RuntimeException("Supabase could not look up the employee account (HTTP {$response->status()}).");
            }

            $users = $response->json('users', []);
            foreach ($users as $user) {
                if (isset($user['id'], $user['email']) && isset($emails[strtolower($user['email'])])) {
                    return $user;
                }
            }

            $page++;
        } while (count($users) === 1000);

        return null;
    }

    private function responseUserId(Response $response, ?string $fallbackId = null): string
    {
        $userId = $response->json('id', $fallbackId);
        if (! is_string($userId) || $userId === '') {
            throw new RuntimeException('Supabase returned an invalid employee account response.');
        }

        return $userId;
    }

    private function client(string $secretKey): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withHeaders(['apikey' => $secretKey])
            ->withToken($secretKey)
            ->timeout((int) config('services.supabase.timeout', 15));
    }
}
