<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'settings' => $this->loadSettings(),
        ]);
    }

    public function update(Request $request)
    {
        $section = $request->input('section', 'branding');

        match ($section) {
            'branding' => $this->updateBranding($request),
            'email' => $this->updateEmail($request),
            'security' => $this->updateSecurity($request),
            'backup' => $this->updateBackup($request),
            'notifications' => $this->updateNotifications($request),
            'maintenance' => $this->updateMaintenance($request),
            'api' => $this->updateApi($request),
            default => null,
        };

        return back()->with('success', ucfirst($section) . ' settings updated successfully.');
    }

    private function loadSettings(): array
    {
        return [
            'branding' => [
                'site_name' => env('APP_NAME', 'RGV Multi-Tech Services'),
                'accent_color' => env('APP_ACCENT_COLOR', '#74c365'),
                'tagline' => env('APP_TAGLINE', ''),
            ],
            'email' => [
                'mailer' => env('MAIL_MAILER', 'smtp'),
                'host' => env('MAIL_HOST', ''),
                'port' => env('MAIL_PORT', '587'),
                'username' => env('MAIL_USERNAME', ''),
                'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                'mail_scheme' => env('MAIL_SCHEME', ''),
                'from_address' => env('MAIL_FROM_ADDRESS', ''),
                'from_name' => env('MAIL_FROM_NAME', ''),
            ],
            'security' => [
                'password_min_length' => env('PASSWORD_MIN_LENGTH', '8'),
                'session_lifetime' => env('SESSION_LIFETIME', '120'),
                'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', '5'),
            ],
            'backup' => [
                'notification_email' => env('BACKUP_NOTIFICATION_EMAIL', ''),
                'admin_notification_email' => env('ADMIN_NOTIFICATION_EMAIL', ''),
                'backup_to_s3' => env('BACKUP_TO_S3', 'false'),
            ],
            'notifications' => [
                'notification_email' => env('SYSTEM_NOTIFICATION_EMAIL', ''),
                'email_enabled' => env('NOTIFICATION_EMAIL_ENABLED', 'true'),
            ],
            'maintenance' => [
                'enabled' => app()->isDownForMaintenance(),
                'message' => env('MAINTENANCE_MESSAGE', 'We are currently performing scheduled maintenance. Please check back shortly.'),
                'allowed_ips' => env('MAINTENANCE_ALLOWED_IPS', ''),
            ],
            'api' => [
                'public_rate_limit' => env('API_PUBLIC_RATE_LIMIT', '30'),
                'standard_rate_limit' => env('API_STANDARD_RATE_LIMIT', '60'),
                'premium_rate_limit' => env('API_PREMIUM_RATE_LIMIT', '300'),
                'admin_rate_limit' => env('API_ADMIN_RATE_LIMIT', '1000'),
            ],
        ];
    }

    private function updateBranding(Request $request): void
    {
        $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'accent_color' => ['required', 'string', 'max:7', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'tagline' => ['nullable', 'string', 'max:255'],
        ]);

        $this->updateEnv('APP_NAME', $request->site_name);
        $this->updateEnv('APP_ACCENT_COLOR', $request->accent_color);
        $this->updateEnv('APP_TAGLINE', $request->tagline ?? '');
    }

    private function updateEmail(Request $request): void
    {
        $request->validate([
            'mailer' => ['required', 'in:smtp,sendmail,mailgun,ses,log'],
            'host' => ['nullable', 'string'],
            'port' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
            'encryption' => ['nullable', 'in:tls,ssl,null'],
            'mail_scheme' => ['nullable', 'in:,smtp,smtps'],
            'from_address' => ['nullable', 'email'],
            'from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $this->updateEnv('MAIL_MAILER', $request->mailer);
        $this->updateEnv('MAIL_HOST', $request->host ?? '');
        $this->updateEnv('MAIL_PORT', $request->port ?? '587');
        $this->updateEnv('MAIL_USERNAME', $request->username ?? '');
        if ($request->filled('password')) {
            $this->updateEnv('MAIL_PASSWORD', $request->password);
        }
        $this->updateEnv('MAIL_ENCRYPTION', $request->encryption ?? 'tls');
        $this->updateEnv('MAIL_SCHEME', $request->mail_scheme ?? '');
        $this->updateEnv('MAIL_FROM_ADDRESS', $request->from_address ?? '');
        $this->updateEnv('MAIL_FROM_NAME', $request->from_name ?? '');
    }

    private function updateSecurity(Request $request): void
    {
        $request->validate([
            'password_min_length' => ['required', 'integer', 'min:6', 'max:32'],
            'session_lifetime' => ['required', 'integer', 'min:5', 'max:1440'],
            'max_login_attempts' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $this->updateEnv('PASSWORD_MIN_LENGTH', (string) $request->password_min_length);
        $this->updateEnv('SESSION_LIFETIME', (string) $request->session_lifetime);
        $this->updateEnv('MAX_LOGIN_ATTEMPTS', (string) $request->max_login_attempts);
    }

    private function updateBackup(Request $request): void
    {
        $request->validate([
            'notification_email' => ['nullable', 'email'],
            'admin_notification_email' => ['nullable', 'email'],
            'backup_to_s3' => ['boolean'],
        ]);

        $this->updateEnv('BACKUP_NOTIFICATION_EMAIL', $request->notification_email ?? '');
        $this->updateEnv('ADMIN_NOTIFICATION_EMAIL', $request->admin_notification_email ?? '');
        $this->updateEnv('BACKUP_TO_S3', $request->boolean('backup_to_s3') ? 'true' : 'false');
    }

    private function updateNotifications(Request $request): void
    {
        $request->validate([
            'notification_email' => ['nullable', 'email'],
            'email_enabled' => ['boolean'],
        ]);

        $this->updateEnv('SYSTEM_NOTIFICATION_EMAIL', $request->notification_email ?? '');
        $this->updateEnv('NOTIFICATION_EMAIL_ENABLED', $request->boolean('email_enabled') ? 'true' : 'false');
    }

    private function updateMaintenance(Request $request): void
    {
        $request->validate([
            'maintenance_enabled' => ['boolean'],
            'message' => ['nullable', 'string', 'max:500'],
            'allowed_ips' => ['nullable', 'string'],
        ]);

        if ($request->boolean('maintenance_enabled') && !app()->isDownForMaintenance()) {
            $args = ['--secret' => \Str::random(32)];

            if ($request->filled('message')) {
                $args['--message'] = $request->message;
            }

            if ($request->filled('allowed_ips')) {
                $ips = array_map('trim', explode(',', $request->allowed_ips));
                $args['--allow-ip'] = $ips;
            }

            \Artisan::call('down', $args);
        } elseif (!$request->boolean('maintenance_enabled') && app()->isDownForMaintenance()) {
            \Artisan::call('up');
        }

        $this->updateEnv('MAINTENANCE_MESSAGE', $request->message ?? '');
        $this->updateEnv('MAINTENANCE_ALLOWED_IPS', $request->allowed_ips ?? '');
    }

    private function updateApi(Request $request): void
    {
        $request->validate([
            'public_rate_limit' => ['required', 'integer', 'min:1'],
            'standard_rate_limit' => ['required', 'integer', 'min:1'],
            'premium_rate_limit' => ['required', 'integer', 'min:1'],
            'admin_rate_limit' => ['required', 'integer', 'min:1'],
        ]);

        $this->updateEnv('API_PUBLIC_RATE_LIMIT', (string) $request->public_rate_limit);
        $this->updateEnv('API_STANDARD_RATE_LIMIT', (string) $request->standard_rate_limit);
        $this->updateEnv('API_PREMIUM_RATE_LIMIT', (string) $request->premium_rate_limit);
        $this->updateEnv('API_ADMIN_RATE_LIMIT', (string) $request->admin_rate_limit);
    }

    private function updateEnv(string $key, string $value): void
    {
        $path = base_path('.env');

        if (!file_exists($path)) {
            return;
        }

        if (str_contains($value, ' ') || str_contains($value, '#') || str_contains($value, '"')) {
            $value = '"' . str_replace('"', '\"', $value) . '"';
        }

        $content = file_get_contents($path);
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $key . '=' . $value, $content);
        } else {
            $content .= PHP_EOL . $key . '=' . $value;
        }

        file_put_contents($path, $content);
    }
}
