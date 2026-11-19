@php
    $toastMessages = [];

    foreach (['success', 'error', 'warning', 'info'] as $flashType) {
        $flashMessage = session($flashType);

        if (is_string($flashMessage) && $flashMessage !== '') {
            $toastMessages[] = ['message' => $flashMessage, 'type' => $flashType];
        }
    }

    $statusMessage = match (session('status')) {
        'profile-updated' => 'Profile updated successfully.',
        'password-updated' => 'Password updated successfully.',
        'verification-link-sent' => 'A new verification link has been sent to your email address.',
        default => session('status'),
    };

    if (is_string($statusMessage) && $statusMessage !== '') {
        $toastMessages[] = ['message' => $statusMessage, 'type' => 'success'];
    }

    if (is_string(session('mfa_status')) && session('mfa_status') !== '') {
        $toastMessages[] = ['message' => session('mfa_status'), 'type' => 'success'];
    }
@endphp

<div
    x-data="toast"
    x-init="loadInitialMessages(JSON.parse($el.dataset.initialMessages))"
    data-initial-messages="{{ json_encode($toastMessages) }}"
    @toast.window="show($event.detail.message, $event.detail.type)"
    class="pointer-events-none fixed right-4 top-4 z-[9999] w-[calc(100vw-2rem)] max-w-[560px] space-y-3"
>
    <template x-for="(toast, index) in toasts" :key="toast.id">
        <div
            x-show="toast.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 -translate-y-2"
            :role="toast.type === 'error' ? 'alert' : 'status'"
            aria-live="polite"
            :class="{
                'border-blue-500 bg-[#e8f0ff] text-[#1749b8]': toast.type === 'success' || toast.type === 'info',
                'border-red-400 bg-[#fff0f0] text-[#a32323]': toast.type === 'error',
                'border-amber-400 bg-[#fff8e5] text-[#8a5200]': toast.type === 'warning'
            }"
            class="pointer-events-auto flex min-h-[76px] items-center gap-3 rounded-2xl border px-5 py-4 shadow-[0_12px_32px_rgba(15,23,42,0.18)]"
        >
            <span
                :class="{
                    'bg-blue-600 text-white': toast.type === 'success' || toast.type === 'info',
                    'bg-red-500 text-white': toast.type === 'error',
                    'bg-amber-500 text-white': toast.type === 'warning'
                }"
                class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full"
            >
                <span class="text-sm font-bold" aria-hidden="true" x-text="toast.type === 'error' ? '×' : (toast.type === 'warning' ? '!' : '✓')"></span>
            </span>
            <span class="min-w-0 flex-1 text-base font-semibold leading-snug" x-text="toast.message"></span>
            <button
                type="button"
                @click="dismiss(index)"
                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-900/10 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                aria-label="Dismiss notification"
            >
                <span class="text-2xl leading-none" aria-hidden="true">×</span>
            </button>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('toast', () => ({
            toasts: [],
            nextId: 0,

            loadInitialMessages(messages = []) {
                messages.forEach(({ message, type }) => this.show(message, type));
            },

            show(message, type = 'success') {
                if (!message) return;

                const toast = { id: this.nextId++, message, type, show: true };
                this.toasts.push(toast);
                window.setTimeout(() => this.dismissById(toast.id), 6000);
            },

            dismiss(index) {
                const toast = this.toasts[index];
                if (toast) this.dismissById(toast.id);
            },

            dismissById(id) {
                const toast = this.toasts.find(item => item.id === id);
                if (!toast) return;

                toast.show = false;
                window.setTimeout(() => {
                    this.toasts = this.toasts.filter(item => item.id !== id);
                }, 250);
            }
        }));
    });
</script>
