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

    <div class="mb-6 bg-[#74c365]/10 border border-[#74c365]/30 p-4 rounded-lg text-center">
        <p class="text-xs text-gray-500 mb-2">Current Code <span class="text-gray-400">(changes every 30s)</span></p>
        <p id="totp-code" class="font-mono text-3xl font-bold text-[#74c365] tracking-[0.3em] select-all">{{ $currentCode }}</p>
        <p class="text-xs text-gray-400 mt-1">Expires in <span id="totp-countdown">30</span>s</p>
    </div>

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

    <script>
    (function() {
        const codeEl = document.getElementById('totp-code');
        const countdownEl = document.getElementById('totp-countdown');
        if (!codeEl || !countdownEl) return;

        function updateCountdown() {
            const seconds = 30 - (Math.floor(Date.now() / 1000) % 30);
            countdownEl.textContent = seconds;
            if (seconds === 30) location.reload();
        }

        setInterval(updateCountdown, 1000);
        updateCountdown();
    })();
    </script>
</x-guest-layout>
