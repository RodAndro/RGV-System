@extends('layouts.admin')

@section('title', 'Site Settings')

@section('content')
<div class="p-6 space-y-6" x-data="{ tab: 'branding' }">
    <section class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Site Settings</h1>

        <!-- Tabs -->
        <div class="flex flex-wrap gap-1 border-b border-gray-200 mb-6">
            @foreach([
                'branding' => 'Branding',
                'email' => 'Email',
                'security' => 'Security',
                'backup' => 'Backup',
                'notifications' => 'Notifications',
                'maintenance' => 'Maintenance',
                'api' => 'API',
            ] as $key => $label)
                <button @click="tab = '{{ $key }}'"
                    class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors"
                    :class="tab === '{{ $key }}' ? 'bg-white dark:bg-gray-800 border border-b-white dark:border-b-gray-800 border-gray-200 dark:border-gray-600 text-[#2563eb] -mb-px' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <!-- Branding -->
        <div x-show="tab === 'branding'" x-cloak>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4 max-w-lg">
                @csrf
                <input type="hidden" name="section" value="branding">

                <div>
                    <label class="block text-sm font-medium text-gray-700">Site Name</label>
                    <input type="text" name="site_name" value="{{ old('site_name', $settings['branding']['site_name']) }}"
                        class="mt-1 block w-full rounded border-gray-300" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $settings['branding']['tagline']) }}"
                        class="mt-1 block w-full rounded border-gray-300" placeholder="Optional tagline for the site">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Accent Color</label>
                    <input type="color" name="accent_color" value="{{ old('accent_color', $settings['branding']['accent_color']) }}"
                        class="mt-1 h-9 w-16 rounded border-gray-300 cursor-pointer">
                </div>

                <button type="submit" class="rounded bg-[#2563eb] px-6 py-2 font-semibold text-white">Save Branding</button>
            </form>
        </div>

        <!-- Email -->
        <div x-show="tab === 'email'" x-cloak>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4 max-w-lg">
                @csrf
                <input type="hidden" name="section" value="email">

                <div>
                    <label class="block text-sm font-medium text-gray-700">Mail Driver</label>
                    <select name="mailer" class="mt-1 block w-full rounded border-gray-300">
                        @foreach(['smtp', 'sendmail', 'mailgun', 'ses', 'log'] as $opt)
                            <option value="{{ $opt }}" {{ $settings['email']['mailer'] === $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">SMTP Host</label>
                    <input type="text" name="host" value="{{ old('host', $settings['email']['host']) }}" class="mt-1 block w-full rounded border-gray-300">
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Port</label>
                        <input type="text" name="port" value="{{ old('port', $settings['email']['port']) }}" class="mt-1 block w-full rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Encryption</label>
                        <select name="encryption" class="mt-1 block w-full rounded border-gray-300">
                            @foreach(['tls', 'ssl', 'null'] as $opt)
                                <option value="{{ $opt }}" {{ $settings['email']['encryption'] === $opt ? 'selected' : '' }}>{{ $opt === 'null' ? 'None' : strtoupper($opt) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Scheme</label>
                        <select name="mail_scheme" class="mt-1 block w-full rounded border-gray-300">
                            <option value="" {{ !$settings['email']['mail_scheme'] ? 'selected' : '' }}>Default</option>
                            @foreach(['smtp', 'smtps'] as $opt)
                                <option value="{{ $opt }}" {{ $settings['email']['mail_scheme'] === $opt ? 'selected' : '' }}>{{ strtoupper($opt) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" value="{{ old('username', $settings['email']['username']) }}" class="mt-1 block w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="relative"><input type="password" name="password" id="smtp-password" class="mt-1 block w-full pr-10 rounded border-gray-300" placeholder="Leave blank to keep unchanged"><button type="button" onclick="togglePassword('smtp-password', 'smtp-eye')" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 focus:outline-none mt-1"><i id="smtp-eye" class="fas fa-eye"></i></button></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">From Address</label>
                        <input type="email" name="from_address" value="{{ old('from_address', $settings['email']['from_address']) }}" class="mt-1 block w-full rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">From Name</label>
                        <input type="text" name="from_name" value="{{ old('from_name', $settings['email']['from_name']) }}" class="mt-1 block w-full rounded border-gray-300">
                    </div>
                </div>

                <button type="submit" class="rounded bg-[#2563eb] px-6 py-2 font-semibold text-white">Save Email Settings</button>
            </form>
        </div>

        <!-- Security -->
        <div x-show="tab === 'security'" x-cloak>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4 max-w-lg">
                @csrf
                <input type="hidden" name="section" value="security">

                <div>
                    <label class="block text-sm font-medium text-gray-700">Password Minimum Length</label>
                    <input type="number" name="password_min_length" value="{{ old('password_min_length', $settings['security']['password_min_length']) }}"
                        class="mt-1 block w-32 rounded border-gray-300" min="6" max="32" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Session Lifetime (minutes)</label>
                    <input type="number" name="session_lifetime" value="{{ old('session_lifetime', $settings['security']['session_lifetime']) }}"
                        class="mt-1 block w-32 rounded border-gray-300" min="5" max="1440" required>
                    <p class="text-xs text-gray-400 mt-1">Default: 120 minutes (2 hours). Max: 1440 (24 hours).</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Max Login Attempts Before Lockout</label>
                    <input type="number" name="max_login_attempts" value="{{ old('max_login_attempts', $settings['security']['max_login_attempts']) }}"
                        class="mt-1 block w-32 rounded border-gray-300" min="1" max="20" required>
                </div>

                <button type="submit" class="rounded bg-[#2563eb] px-6 py-2 font-semibold text-white">Save Security</button>
            </form>
        </div>

        <!-- Backup -->
        <div x-show="tab === 'backup'" x-cloak>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4 max-w-lg">
                @csrf
                <input type="hidden" name="section" value="backup">

                <div>
                    <label class="block text-sm font-medium text-gray-700">Backup Notification Email</label>
                    <input type="email" name="notification_email" value="{{ old('notification_email', $settings['backup']['notification_email']) }}"
                        class="mt-1 block w-full rounded border-gray-300" placeholder="admin@example.com">
                    <p class="text-xs text-gray-400 mt-1">Email for backup success/failure alerts. Leave blank to disable.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Admin Update Email</label>
                    <input type="email" name="admin_notification_email" value="{{ old('admin_notification_email', $settings['backup']['admin_notification_email']) }}"
                        class="mt-1 block w-full rounded border-gray-300" placeholder="admin@example.com">
                    <p class="text-xs text-gray-400 mt-1">Email for entity update notifications (bookings, inventory, borrow requests).</p>
                </div>

                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="backup_to_s3" value="1" {{ $settings['backup']['backup_to_s3'] === 'true' ? 'checked' : '' }} class="rounded border-gray-300">
                        <span class="text-sm font-medium text-gray-700">Backup to S3 Cloud Storage</span>
                    </label>
                </div>

                <div class="text-sm text-gray-500 mt-2">
                    <a href="{{ route('admin.backups.settings') }}" class="text-[#1e40af] hover:underline">
                        <i class="fas fa-external-link-alt mr-1"></i>Advanced backup settings
                    </a>
                </div>

                <button type="submit" class="rounded bg-[#2563eb] px-6 py-2 font-semibold text-white">Save Backup</button>
            </form>
        </div>

        <!-- Notifications -->
        <div x-show="tab === 'notifications'" x-cloak>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4 max-w-lg">
                @csrf
                <input type="hidden" name="section" value="notifications">

                <div>
                    <label class="block text-sm font-medium text-gray-700">System Notification Email</label>
                    <input type="email" name="notification_email" value="{{ old('notification_email', $settings['notifications']['notification_email']) }}"
                        class="mt-1 block w-full rounded border-gray-300" placeholder="admin@example.com">
                    <p class="text-xs text-gray-400 mt-1">Default email for system-generated notification emails.</p>
                </div>

                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="email_enabled" value="1" {{ $settings['notifications']['email_enabled'] === 'true' ? 'checked' : '' }} class="rounded border-gray-300">
                        <span class="text-sm font-medium text-gray-700">Enable global email notifications</span>
                    </label>
                    <p class="text-xs text-gray-400 mt-1 ml-6">When disabled, no email notifications will be sent from the system.</p>
                </div>

                <button type="submit" class="rounded bg-[#2563eb] px-6 py-2 font-semibold text-white">Save Notifications</button>
            </form>
        </div>

        <!-- Maintenance -->
        <div x-show="tab === 'maintenance'" x-cloak>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4 max-w-lg">
                @csrf
                <input type="hidden" name="section" value="maintenance">

                <div class="p-4 rounded-lg {{ $settings['maintenance']['enabled'] ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200' }}">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="maintenance_enabled" value="1" {{ $settings['maintenance']['enabled'] ? 'checked' : '' }} class="rounded border-gray-300">
                        <span class="text-sm font-medium {{ $settings['maintenance']['enabled'] ? 'text-red-700' : 'text-green-700' }}">
                            <i class="fas {{ $settings['maintenance']['enabled'] ? 'fa-lock' : 'fa-check-circle' }} mr-1"></i>
                            Maintenance Mode {{ $settings['maintenance']['enabled'] ? 'Enabled' : 'Disabled' }}
                        </span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Maintenance Message</label>
                    <textarea name="message" rows="3" class="mt-1 block w-full rounded border-gray-300">{{ old('message', $settings['maintenance']['message']) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Allowed IPs (comma-separated)</label>
                    <input type="text" name="allowed_ips" value="{{ old('allowed_ips', $settings['maintenance']['allowed_ips']) }}"
                        class="mt-1 block w-full rounded border-gray-300" placeholder="192.168.1.1,10.0.0.1">
                    <p class="text-xs text-gray-400 mt-1">IPs that can still access the site during maintenance.</p>
                </div>

                <button type="submit" class="rounded bg-[#2563eb] px-6 py-2 font-semibold text-white">Save Maintenance</button>
            </form>
        </div>

        <!-- API -->
        <div x-show="tab === 'api'" x-cloak>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4 max-w-lg">
                @csrf
                <input type="hidden" name="section" value="api">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Public Tier (req/min)</label>
                        <input type="number" name="public_rate_limit" value="{{ old('public_rate_limit', $settings['api']['public_rate_limit']) }}"
                            class="mt-1 block w-full rounded border-gray-300" min="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Standard Tier (req/min)</label>
                        <input type="number" name="standard_rate_limit" value="{{ old('standard_rate_limit', $settings['api']['standard_rate_limit']) }}"
                            class="mt-1 block w-full rounded border-gray-300" min="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Premium Tier (req/min)</label>
                        <input type="number" name="premium_rate_limit" value="{{ old('premium_rate_limit', $settings['api']['premium_rate_limit']) }}"
                            class="mt-1 block w-full rounded border-gray-300" min="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Admin Tier (req/min)</label>
                        <input type="number" name="admin_rate_limit" value="{{ old('admin_rate_limit', $settings['api']['admin_rate_limit']) }}"
                            class="mt-1 block w-full rounded border-gray-300" min="1" required>
                    </div>
                </div>

                <button type="submit" class="rounded bg-[#2563eb] px-6 py-2 font-semibold text-white">Save API Settings</button>
            </form>
        </div>
    </section>
</div>

<style>[x-cloak] { display: none !important; }</style>
<script>function togglePassword(i, e) { const a = document.getElementById(i), b = document.getElementById(e); a.type === 'password' ? (a.type = 'text', b.classList.replace('fa-eye', 'fa-eye-slash')) : (a.type = 'password', b.classList.replace('fa-eye-slash', 'fa-eye')) }</script>@endsection
