@props([
    'name',
    'action',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to continue?',
    'confirmText' => 'Confirm',
    'method' => 'POST',
    'passwordRequired' => false,
    'passwordLabel' => 'Enter your password to confirm',
    'passwordErrorKey' => 'password',
    'passwordErrorMessages' => null,
    'confirmationKey' => null,
    'show' => false,
    'formId' => null,
    'enctype' => null,
    'confirmClass' => 'bg-red-600 hover:bg-red-700',
    'icon' => 'fa-exclamation-triangle',
])

@php($passwordId = 'confirm-password-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $name))

<x-modal :name="$name" maxWidth="xl" panelClass="bg-white" :show="$show" focusable>
    <form @if($formId) id="{{ $formId }}" @endif @if($enctype) enctype="{{ $enctype }}" @endif method="POST" action="{{ $action }}" data-action-template="{{ $action }}" class="p-7 sm:p-8 text-gray-900">
        @csrf
        @if(strtoupper($method) !== 'POST')
            @method($method)
        @endif
        @if($confirmationKey)
            <input type="hidden" name="_confirmation_action" value="{{ $confirmationKey }}">
        @endif

        <div class="flex items-center gap-4 mb-5">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-lg" aria-hidden="true"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>
        </div>

        @if($message !== '')
            <p class="text-base leading-6 text-gray-600 mb-5">{{ $message }}</p>
        @endif

        @if(trim((string) $slot) !== '')
            <div class="mb-5">{{ $slot }}</div>
        @endif

        @if($passwordRequired)
            <div class="mb-5">
                <label for="{{ $passwordId }}" class="block text-sm font-semibold text-gray-700 mb-2">{{ $passwordLabel }}</label>
                <input id="{{ $passwordId }}" type="password" name="password" required autocomplete="current-password"
                    class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm focus:border-red-400 focus:ring-2 focus:ring-red-100">
                <x-input-error :messages="$passwordErrorMessages ?? $errors->get($passwordErrorKey)" class="mt-2" />
            </div>
        @endif

        <div class="flex flex-wrap justify-end gap-3">
            <button type="button" @click="$dispatch('close-modal', '{{ $name }}')"
                class="px-5 py-3 rounded-xl bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors text-sm font-semibold">
                Cancel
            </button>
            <button type="submit" class="px-5 py-3 rounded-xl text-white transition-colors text-sm font-semibold {{ $confirmClass }}">
                <i class="fas {{ $icon }} mr-2" aria-hidden="true"></i>{{ $confirmText }}
            </button>
        </div>
    </form>
</x-modal>
