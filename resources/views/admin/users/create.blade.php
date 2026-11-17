@extends('layouts.admin')

@section('title', 'Add User - Admin Dashboard')

@section('header', 'Add New User')

@section('content')
<div class="p-4 md:p-8">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-plus-circle"></i>User Information</h2>
    </div>
    <div class="card-mantis p-6 md:p-8">
        @if(session('success'))
            <div class="bg-gradient-to-r from-[#eff6ff] to-[#dbeafe] dark:from-blue-900/20 dark:to-blue-800/10 border border-[#2563eb] dark:border-blue-700 text-[#1e40af] dark:text-blue-300 px-4 py-3 rounded-xl mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                        placeholder="Enter user's full name">
                    @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                        placeholder="user@example.com">
                    @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                        placeholder="+63 912 345 6789">
                    @error('phone')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role *</label>
                    <select name="role" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                        <option value="">Select role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password *</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required autocomplete="new-password"
                            class="password-field w-full pl-4 pr-12 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="Enter secure password">
                        <button type="button" id="password-toggle" data-password-toggle="password" data-password-icon="eye-icon"
                            aria-label="Show password" aria-pressed="false"
                            class="absolute inset-y-0 right-0 hidden items-center px-3 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                            <i id="eye-icon" class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Minimum 8 characters.</p>
                    @error('password')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password *</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                            class="password-field w-full pl-4 pr-12 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="Confirm password">
                        <button type="button" id="password-confirmation-toggle" data-password-toggle="password_confirmation" data-password-icon="eye-icon-confirm"
                            aria-label="Show password" aria-pressed="false"
                            class="absolute inset-y-0 right-0 hidden items-center px-3 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                            <i id="eye-icon-confirm" class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password_confirmation')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address</label>
                    <textarea name="address" rows="3"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                        placeholder="Enter user's address">{{ old('address') }}</textarea>
                    @error('address')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="section-divider"></div>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="{{ route('admin.users.index') }}" class="px-6 py-3 border-2 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-all font-medium">Cancel</a>
                <button type="submit" class="btn-mantis"><i class="fas fa-user-plus mr-2"></i>Create User</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<style>
    /* Keep the custom toggle the only control inside the password fields.
       Edge/IE (::-ms-reveal) and Chromium autofill draw their own icons. */
    .password-field::-ms-reveal,
    .password-field::-ms-clear {
        display: none;
    }

    .password-field::-webkit-credentials-auto-fill-button,
    .password-field::-webkit-contacts-auto-fill-button {
        visibility: hidden;
        pointer-events: none;
    }
</style>
<script>
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        var input = document.getElementById(button.dataset.passwordToggle);
        var icon = document.getElementById(button.dataset.passwordIcon);
        if (!input || !icon) return;

        function syncToggle() {
            var hasValue = input.value.length > 0;
            button.classList.toggle('hidden', !hasValue);
            button.classList.toggle('flex', hasValue);

            if (!hasValue) {
                // Never leave a cleared field revealed or its icon stuck.
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                button.setAttribute('aria-label', 'Show password');
                button.setAttribute('aria-pressed', 'false');
            }
        }

        button.addEventListener('click', function () {
            var reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !reveal);
            icon.classList.toggle('fa-eye-slash', reveal);
            button.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
            button.setAttribute('aria-pressed', String(reveal));
            input.focus();
        });

        input.addEventListener('input', syncToggle);
        input.addEventListener('change', syncToggle);
        syncToggle();
    });
</script>
@endpush
