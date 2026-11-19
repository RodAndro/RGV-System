<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="!text-gray-900 font-semibold" />
            <x-text-input id="email" class="block mt-1 w-full bg-white dark:bg-white dark:text-gray-900 border-2 border-gray-300 dark:border-gray-500" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="!text-gray-900 font-semibold" />
            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10 bg-white dark:bg-white dark:text-gray-900 border-2 border-gray-300 dark:border-gray-500" type="password" name="password" required autocomplete="new-password" />
                <button type="button" onclick="togglePassword('password', this)" class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="far fa-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="!text-gray-900 font-semibold" />
            <div class="relative">
                <x-text-input id="password_confirmation" class="block mt-1 w-full pr-10 bg-white dark:bg-white dark:text-gray-900 border-2 border-gray-300 dark:border-gray-500"
                                    type="password"
                                    name="password_confirmation" required autocomplete="new-password" />
                <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="far fa-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <script>
            function togglePassword(inputId, button) {
                const input = document.getElementById(inputId);
                const icon = button.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        </script>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
