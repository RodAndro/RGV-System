<div x-data="toast" @toast.window="show($event.detail.message, $event.detail.type)" class="fixed top-4 right-4 z-[9999] space-y-2">
    <template x-for="(t, i) in toasts" :key="i">
        <div x-show="t.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            :class="t.type === 'success' ? 'bg-gradient-to-r from-[#f0f9ef] to-[#e0f3df] dark:from-green-900/30 dark:to-green-800/20 border-[#74c365] dark:border-green-700 text-[#468a3f] dark:text-green-300' : 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300'"
            class="px-4 py-3 rounded-xl border shadow-lg flex items-center space-x-3 min-w-[300px] max-w-md">
            <i :class="t.type === 'success' ? 'fas fa-check-circle text-[#74c365]' : 'fas fa-exclamation-circle text-red-500'" class="text-lg"></i>
            <span class="text-sm font-medium" x-text="t.message"></span>
            <button @click="dismiss(i)" class="ml-auto text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('toast', () => ({
            toasts: [],
            show(message, type = 'success') {
                const toast = { message, type, show: true };
                this.toasts.push(toast);
                setTimeout(() => this.dismiss(this.toasts.indexOf(toast)), 5000);
            },
            dismiss(index) {
                if (this.toasts[index]) {
                    this.toasts[index].show = false;
                    setTimeout(() => this.toasts.splice(index, 1), 300);
                }
            }
        }));
    });
</script>
