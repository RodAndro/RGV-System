@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.employee')

@section('title', 'Profile Settings')

@section('header', 'Profile Settings')

@section('content')
<div class="p-8">
    <div class="max-w-5xl mx-auto space-y-6">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card-mantis p-6">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-user-edit"></i>Profile Information</h2>
                </div>
                <p class="text-sm text-gray-500 mb-6">Update your name and email address.</p>
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="address" rows="2"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] bg-gray-50">{{ old('address', auth()->user()->address) }}</textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-mantis">Save Changes</button>
                    </div>
                    @if(session('status') === 'profile-updated')
                        <p class="text-sm text-green-600">Profile updated successfully.</p>
                    @endif
                </form>
            </div>

            <div class="card-mantis p-6">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-lock"></i>Change Password</h2>
                </div>
                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password" name="current_password" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] bg-gray-50">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-mantis">Update Password</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-mantis p-6">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-shield-alt"></i>Two-Factor Authentication</h2>
            </div>
            @if(auth()->user()->mfa_enabled)
                <p class="text-sm text-green-700 font-medium mb-3">Two-factor authentication is enabled.</p>
                @if(auth()->user()->mfa_secret)
                <div class="bg-[#2563eb]/10 border border-[#2563eb]/30 p-4 rounded-lg mb-4">
                    <p class="text-xs text-gray-500 mb-2">Current Verification Code <span class="text-gray-400">(refreshes every 30s)</span>:</p>
                    <p id="totp-code" class="font-mono text-3xl font-bold text-[#2563eb] tracking-[0.3em] select-all">{{ auth()->user()->getCurrentTotpCode() }}</p>
                    <p class="text-xs text-gray-400 mt-1">Expires in <span id="totp-countdown">30</span>s</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <p class="text-xs text-gray-500 mb-2">Setup Key (enter in Google Authenticator or any TOTP app):</p>
                    <p class="font-mono text-lg font-bold text-gray-800 select-all">{{ auth()->user()->mfa_secret }}</p>
                </div>
                @endif
                @if(auth()->user()->mfa_recovery_codes)
                <details class="mb-4">
                    <summary class="text-sm text-gray-600 cursor-pointer hover:text-gray-800">View Recovery Codes</summary>
                    <div class="mt-2 bg-gray-50 p-3 rounded-lg">
                        @foreach(auth()->user()->mfa_recovery_codes as $code)
                            <p class="text-xs font-mono select-all">{{ $code }}</p>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Save these in a safe place. Each code works once.</p>
                </details>
                @endif
                <form method="POST" action="{{ route('profile.mfa.disable') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm">Disable MFA</button>
                </form>
            @else
                <p class="text-sm text-gray-600 mb-3">Add extra security using an authenticator app like Google Authenticator.</p>
                <form method="POST" action="{{ route('profile.mfa.enable') }}">
                    @csrf
                    <button type="submit" class="btn-mantis">Enable Two-Factor Authentication</button>
                </form>
            @endif
        </div>

        <div class="card-mantis p-6">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-bell"></i>Notification Preferences</h2>
            </div>
            <p class="text-sm text-gray-600 mb-4">Choose how you receive each notification type.</p>
            @php $prefs = auth()->user()->notificationPreferences; @endphp
            <form method="POST" action="{{ route('profile.notification-preferences.update') }}">
                @csrf
                @method('PUT')
                <div class="space-y-3">
                    @foreach(['booking' => 'Work Requests', 'borrow_request' => 'Borrow Requests', 'inventory' => 'Inventory', 'system' => 'System', 'user' => 'Account'] as $type => $label)
                        @php $p = $prefs->where('type', $type)->first(); @endphp
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                            <div class="flex space-x-4">
                                <input type="hidden" name="preferences[{{ $loop->index }}][type]" value="{{ $type }}">
                                <label class="flex items-center space-x-1 text-sm"><input type="checkbox" name="preferences[{{ $loop->index }}][email_enabled]" value="1" {{ $p && $p->email_enabled ? 'checked' : '' }} class="rounded border-gray-300 text-[#2563eb]"> Email</label>
                                <label class="flex items-center space-x-1 text-sm"><input type="checkbox" name="preferences[{{ $loop->index }}][in_app_enabled]" value="1" {{ $p && $p->in_app_enabled ? 'checked' : '' }} class="rounded border-gray-300 text-[#2563eb]"> In-App</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-mantis">Save Preferences</button>
                </div>
            </form>
        </div>

    </div>
</div>

@if(auth()->user()->mfa_enabled && auth()->user()->mfa_secret)
<script>
(function() {
    const codeEl = document.getElementById('totp-code');
    const countdownEl = document.getElementById('totp-countdown');
    if (!codeEl || !countdownEl) return;

    function updateCountdown() {
        const seconds = 30 - (Math.floor(Date.now() / 1000) % 30);
        countdownEl.textContent = seconds;
        if (seconds === 30) {
            fetch('{{ route("profile.totp-code") }}')
                .then(r => r.json())
                .then(data => { if (data.code) codeEl.textContent = data.code; });
        }
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();
})();
</script>
@endif
@endsection
