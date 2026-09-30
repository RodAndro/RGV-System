<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class GoogleDriveService
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'gif', 'pdf'];

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart';

    private const FILES_URL = 'https://www.googleapis.com/drive/v3/files';

    private const DRIVE_SCOPE = 'https://www.googleapis.com/auth/drive';

    public function __construct(private readonly Client $http)
    {
    }

    /**
     * Upload return-proof evidence to the configured Drive folder using a
     * service account, so no Google credentials ever reach the mobile client.
     *
     * @return array{drive_file_id: string, file_name: string, mime_type: string}
     */
    public function uploadEvidence(UploadedFile $file, string $surname): array
    {
        $credentials = $this->credentials();
        $folderId = config('services.google_drive.folder_id');

        if (! $folderId) {
            throw new RuntimeException('Google Drive folder is not configured.');
        }

        $accessToken = $this->accessToken($credentials);

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $extension = 'jpg';
        }

        $baseName = sprintf('%s - %s', $this->sanitizeSurname($surname), now()->format('n/j/Y'));
        $mimeType = $file->getMimeType() ?: 'image/jpeg';

        $fileName = $this->uniqueFileName($accessToken, $folderId, $baseName, $extension);

        $metadata = json_encode(['name' => $fileName, 'parents' => [$folderId]]);

        $boundary = 'rvg_'.bin2hex(random_bytes(8));
        $body = "--{$boundary}\r\n"
            ."Content-Type: application/json; charset=UTF-8\r\n\r\n"
            .$metadata."\r\n"
            ."--{$boundary}\r\n"
            ."Content-Type: {$mimeType}\r\n\r\n"
            .$file->get()."\r\n"
            ."--{$boundary}--";

        try {
            $response = $this->http->post(self::UPLOAD_URL, [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'multipart/related; boundary='.$boundary,
                ],
                'body' => $body,
            ]);
        } catch (\Throwable $e) {
            throw new RuntimeException('Google Drive upload failed: '.$e->getMessage(), 0, $e);
        }

        $result = json_decode((string) $response->getBody(), true);

        if (! isset($result['id'])) {
            throw new RuntimeException('Google Drive upload failed: no file id returned.');
        }

        return [
            'drive_file_id' => $result['id'],
            'file_name' => $fileName,
            'mime_type' => $mimeType,
        ];
    }

    private function credentials(): array
    {
        $credentialsPath = config('services.google_drive.credentials_path');
        $credentialsJson = config('services.google_drive.credentials_json');

        if ($credentialsPath && is_file($credentialsPath)) {
            $decoded = json_decode((string) file_get_contents($credentialsPath), true);
        } elseif ($credentialsJson) {
            $decoded = json_decode($credentialsJson, true);
        } else {
            throw new RuntimeException('Google Drive service-account credentials are not configured.');
        }

        if (! is_array($decoded) || empty($decoded['client_email']) || empty($decoded['private_key'])) {
            throw new RuntimeException('Google Drive service-account credentials are invalid.');
        }

        return $decoded;
    }

    private function accessToken(array $credentials): string
    {
        $header = $this->base64Url(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => $credentials['private_key_id'] ?? null,
        ]));

        $claims = [
            'iss' => $credentials['client_email'],
            'scope' => self::DRIVE_SCOPE,
            'aud' => $credentials['token_uri'] ?? self::TOKEN_URL,
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        if ($subject = config('services.google_drive.subject')) {
            $claims['sub'] = $subject;
        }

        $unsigned = $header.'.'.$this->base64Url(json_encode($claims));

        if (! openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign Google Drive service-account JWT.');
        }

        $jwt = $unsigned.'.'.$this->base64Url($signature);

        try {
            $response = $this->http->post($claims['aud'], [
                'form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ],
            ]);
        } catch (\Throwable $e) {
            throw new RuntimeException('Google Drive token exchange failed: '.$e->getMessage(), 0, $e);
        }

        $token = json_decode((string) $response->getBody(), true);

        if (empty($token['access_token'])) {
            throw new RuntimeException('Google Drive token exchange returned no access token.');
        }

        return $token['access_token'];
    }

    private function uniqueFileName(string $accessToken, string $folderId, string $baseName, string $extension): string
    {
        $candidate = sprintf('%s.%s', $baseName, $extension);

        try {
            $response = $this->http->get(self::FILES_URL, [
                'headers' => ['Authorization' => 'Bearer '.$accessToken],
                'query' => [
                    'q' => sprintf("'%s' in parents and trashed = false", $folderId),
                    'fields' => 'files(name)',
                    'pageSize' => 1000,
                ],
            ]);

            $existing = collect(json_decode((string) $response->getBody(), true)['files'] ?? [])
                ->pluck('name')
                ->all();

            $counter = 2;
            while (in_array($candidate, $existing, true)) {
                $candidate = sprintf('%s (%d).%s', $baseName, $counter, $extension);
                $counter++;
            }
        } catch (\Throwable) {
            return sprintf('%s (%s).%s', $baseName, now()->format('His'), $extension);
        }

        return $candidate;
    }

    private function sanitizeSurname(string $surname): string
    {
        $surname = preg_replace('/[^A-Za-z0-9 _-]/', '', $surname) ?? '';
        $surname = preg_replace('/\s+/', ' ', trim($surname)) ?? '';

        return $surname !== '' ? $surname : 'User';
    }

    private function base64Url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
