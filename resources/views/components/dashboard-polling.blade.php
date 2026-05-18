<div x-data="{
    stats: {},
    lastUpdated: null,
    async fetchStats(params = '') {
        try {
            const res = await fetch('{{ route('admin.dashboard.stats') }}' + params);
            this.stats = await res.json();
            this.lastUpdated = new Date();
        } catch (e) {}
    },
    applyFilter(detail) {
        const p = new URLSearchParams();
        if (detail.dateFrom) p.set('date_from', detail.dateFrom);
        if (detail.dateTo) p.set('date_to', detail.dateTo);
        this.fetchStats('?' + p.toString());
    },
    init() {
        this.fetchStats();
        setInterval(() => this.fetchStats(), 30000);
        window.addEventListener('dashboard-filter', e => this.applyFilter(e.detail));
    }
}" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500">Total Work Requests</p>
            <p class="text-2xl font-bold text-gray-800 mt-1" x-text="stats.total_bookings || 0">-</p>
        </div>
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending Requests</p>
            <p class="text-2xl font-bold text-amber-600 mt-1" x-text="stats.pending_bookings || 0">-</p>
        </div>
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500">Inventory Items</p>
            <p class="text-2xl font-bold text-gray-800 mt-1" x-text="stats.inventory_count || 0">-</p>
        </div>
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500">Low Stock Alerts</p>
            <p class="text-2xl font-bold text-red-600 mt-1" x-text="stats.low_stock_alerts || 0">-</p>
        </div>
    </div>

    <div x-show="stats.system?.storage_warning" class="card-mantis p-4 bg-red-50 border-red-200">
        <p class="text-sm font-medium text-red-700 dark:text-red-300"><i class="fas fa-exclamation-triangle mr-2"></i>Storage running low — less than 15% free</p>
    </div>

    <p class="text-xs text-gray-400 text-right" x-show="lastUpdated">
        Last updated: <span x-text="lastUpdated?.toLocaleTimeString()"></span>
    </p>
</div>

an>
    </p>
</div>
