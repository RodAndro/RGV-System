<div class="relative"
     x-data="{
        unreadCount: {{ auth()->user()->unreadNotifications()->count() }},
        notifications: [],
        open: false,
        markAllInFlight: false,
        baseUrl: '{{ auth()->user()->isAdmin() ? 'admin' : 'employee' }}',
        async fetchNotifications() {
            try {
                const url = '{{ auth()->user()->isAdmin() ? route('admin.notifications.unread-count') : route('employee.notifications.unread-count') }}';
                const res = await fetch(url);
                const data = await res.json();
                this.unreadCount = data.count;
                this.notifications = data.notifications;
            } catch(e) {}
        },
        async markOneRead(id) {
            try {
                const url = `/${this.baseUrl}/notifications/${id}/mark-read`;
                await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
            } catch(e) {}
        },
        async openNotification(notification) {
            this.notifications = this.notifications.filter(n => n.id !== notification.id);
            if (this.unreadCount > 0) this.unreadCount--;
            this.markOneRead(notification.id);
            window.location.href = notification.link;
        },
        async markAllRead() {
            if (this.markAllInFlight) return;
            this.markAllInFlight = true;
            try {
                const url = '{{ auth()->user()->isAdmin() ? route('admin.notifications.mark-all-read') : route('employee.notifications.mark-all-read') }}';
                await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
                this.unreadCount = 0;
                this.notifications = [];
            } catch(e) {} finally {
                this.markAllInFlight = false;
            }
        },
        init() {
            this.fetchNotifications();
            setInterval(() => this.fetchNotifications(), 30000);
        }
    }">
    <button @click="open = !open" class="relative">
        <i class="fas fa-bell text-gray-600 text-xl cursor-pointer hover:text-[#2563eb] transition-colors"></i>
        <span x-show="unreadCount > 0"
            x-cloak
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"
            x-text="unreadCount"></span>
    </button>

    <div x-show="open" @click.away="open = false" x-transition
        class="absolute right-0 mt-3 w-96 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 z-50 max-h-96 overflow-hidden">
        <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-semibold text-gray-800 dark:text-gray-100">Notifications</h3>
            <div class="flex space-x-2">
                <button x-show="unreadCount > 0" @click="markAllRead()"
                    class="text-xs text-[#2563eb] hover:underline" :disabled="markAllInFlight">
                    Mark all read
                </button>
            </div>
        </div>
        <div class="overflow-y-auto max-h-72">
            <template x-if="notifications.length === 0">
                <div class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                    <i class="fas fa-bell-slash text-2xl mb-2 block"></i>
                    <p class="text-sm">No new notifications</p>
                </div>
            </template>
            <template x-for="n in notifications" :key="n.id">
                <a @click.prevent="openNotification(n)"
                   class="block px-4 py-3 border-b border-gray-50 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                    <div class="flex items-start">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate" x-text="n.title || 'Notification'"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5" x-text="n.message || ''"></p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" x-text="n.created_at"></p>
                        </div>
                        <div class="ml-3 w-2 h-2 bg-[#2563eb] rounded-full mt-2 flex-shrink-0"></div>
                    </div>
                </a>
            </template>
        </div>
        <div class="p-3 border-t border-gray-100 dark:border-gray-700 text-center">
            <a href="{{ auth()->user()->isAdmin() ? route('admin.notifications.index') : route('employee.notifications.index') }}"
               class="text-sm text-[#2563eb] font-medium hover:underline">
                View All Notifications
            </a>
        </div>
    </div>
</div>
