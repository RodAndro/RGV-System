<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">Two-Factor Authentication</h2>
        <p class="mt-2 text-sm text-gray-600">Enter the 6-digit code from your authenticator app.</p>
    </div>

    @if($errors->any())
        <div class="mb-4 font-medium text-sm text-red-600 bg-red-50 p-3 rounded-lg">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('mfa.verify') }}">
        @csrf
        <div>
            <x-input-label for="code" value="Authenticator Code" class="!text-gray-900 font-semibold" />
            <x-text-input id="code" class="block mt-1 w-full text-center text-2xl tracking-widest bg-white dark:bg-white dark:text-gray-900 border-2 border-gray-300 dark:border-gray-500" type="text" name="code" maxlength="6" placeholder="000000" required autofocus autocomplete="one-time-code" inputmode="numeric" />
        </div>
        <div class="flex items-center justify-center mt-6">
            <button type="submit" class="btn-mantis px-8 py-3">Verify</button>
        </div>
    </form>

</x-guest-layout>
