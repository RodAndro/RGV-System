<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="!text-gray-900 font-semibold" />
            <x-text-input id="email" class="block mt-1 w-full bg-white dark:bg-white dark:text-gray-900 border-2 border-gray-300 dark:border-gray-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="!text-gray-900 font-semibold" />

            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10 bg-white dark:bg-white dark:text-gray-900 border-2 border-gray-300 dark:border-gray-500"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />

                <button type="button" onclick="togglePassword()"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 focus:outline-none">
                    <i id="eye-icon" class="fas fa-eye"></i>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        @if($bruteForceDetected ?? false)
            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-sm text-amber-700 mb-2">
                    <i class="fas fa-shield-halved mr-1"></i>
                    Too many failed login attempts detected. Please verify you are human.
                </p>
                <x-input-label for="captcha" class="!text-gray-900 font-semibold" :value="'What is ' . $captchaA . ' + ' . $captchaB . '?'" />
                <x-text-input id="captcha" class="block mt-1 w-full bg-white dark:bg-white dark:text-gray-900 border-2 border-gray-300 dark:border-gray-500" type="number" name="captcha" required autocomplete="off" />
                <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
            </div>
        @endif

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</x-guest-layout>
