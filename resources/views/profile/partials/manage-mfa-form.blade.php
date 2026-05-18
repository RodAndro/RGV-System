<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Two-Factor Authentication</h2>
        <p class="mt-1 text-sm text-gray-600">Add additional security to your account using an email verification code.</p>
    </header>

    @if(session('mfa_status'))
        <div class="mt-4 text-sm text-green-600">
            {{ session('mfa_status') }}
        </div>
    @endif

    @if(auth()->user()->mfa_enabled)
        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-700 font-medium">Two-factor authentication is enabled.</p>

            @if(auth()->user()->mfa_recovery_codes)
                <details class="mt-3">
                    <summary class="text-sm text-green-600 cursor-pointer hover:underline">View recovery codes</summary>
                    <ul class="mt-2 space-y-1">
                        @foreach(auth()->user()->mfa_recovery_codes as $code)
                            <li class="text-xs font-mono bg-white px-2 py-1 rounded border border-gray-200">{{ $code }}</li>
                        @endforeach
                    </ul>
                    <p class="text-xs text-gray-500 mt-2">Store these codes in a safe place. Each code can only be used once.</p>
                </details>
            @endif

            <form method="POST" action="{{ route('profile.mfa.disable') }}" class="mt-4">
                @csrf
                <x-danger-button>Disable Two-Factor Authentication</x-danger-button>
            </form>
        </div>
    @else
        <form method="POST" action="{{ route('profile.mfa.enable') }}" class="mt-4">
            @csrf
            <x-primary-button>Enable Two-Factor Authentication</x-primary-button>
        </form>
    @endif
</section>
