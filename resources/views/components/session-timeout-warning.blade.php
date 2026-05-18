<?php $lifetime = (int) config('session.lifetime', 120); ?>
<div x-data="{
    show: false,
    countdown: 120,
    timer: null,
    init() {
        const warnAt = {{ $lifetime * 60 - 120 }};
        this.countdown = 120;
        this.timer = setTimeout(() => {
            this.show = true;
            this.startCountdown();
        }, warnAt * 1000);
    },
    startCountdown() {
        this.timer = setInterval(() => {
            this.countdown--;
            if (this.countdown <= 0) {
                clearInterval(this.timer);
                window.location.reload();
            }
        }, 1000);
    },
    extend() {
        fetch('{{ route('session.extend') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            }
        }).then(() => {
            this.show = false;
            clearInterval(this.timer);
            this.init();
        });
    }
}" x-show="show" x-cloak class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
        <div class="text-center">
            <i class="fas fa-clock text-amber-500 text-4xl mb-4"></i>
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-2">Session Expiring</h3>
            <p class="text-gray-600 mb-1">Your session will expire in</p>
            <p class="text-3xl font-bold text-[#74c365] mb-4" x-text="Math.floor(countdown / 60) + ':' + String(countdown % 60).padStart(2, '0')"></p>
            <p class="text-sm text-gray-500 mb-6">You'll be logged out automatically. Extend your session to continue working.</p>
            <div class="flex justify-center space-x-3">
                <button @click="extend()" class="btn-mantis px-6 py-3">
                    <i class="fas fa-clock mr-2"></i>Extend Session
                </button>
            </div>
        </div>
    </div>
</div>
<style>[x-cloak] { display: none !important; }</style>
