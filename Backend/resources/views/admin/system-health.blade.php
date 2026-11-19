@extends('layouts.admin')

@section('title', 'System Health & Performance')

@section('content')
<div class="p-6" x-data="{
    stats: {},
    lastUpdated: null,
    async fetchStats() {
        try {
            const res = await fetch('{{ route('admin.dashboard.stats') }}');
            this.stats = await res.json();
            this.lastUpdated = new Date();
        } catch(e) {}
    },
    init() {
        this.fetchStats();
        setInterval(() => this.fetchStats(), 30000);
    }
}">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-heartbeat"></i>System Health & Performance</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-hdd mr-1"></i>Disk Usage</p>
            <p class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-1">
                <span x-text="stats.system?.disk_usage_percent || 0"></span>%
            </p>
            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5 mt-2">
                <div class="h-2.5 rounded-full transition-all" :class="(stats.system?.disk_usage_percent || 0) > 85 ? 'bg-red-500' : 'bg-[#2563eb]'"
                    :style="'width: ' + (stats.system?.disk_usage_percent || 0) + '%'"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1">
                <span x-text="stats.system?.disk_free_gb ?? '—'"></span> GB free of
                <span x-text="stats.system?.disk_total_gb ?? '—'"></span> GB
            </p>
        </div>
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-server mr-1"></i>Server Uptime</p>
            <p class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-1">
                <span x-text="stats.system?.server_uptime_days ?? '—'"></span>
                <span class="text-sm text-gray-400" x-show="stats.system?.server_uptime_days"> days</span>
            </p>
        </div>
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-database mr-1"></i>Database Size</p>
            <p class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-1">
                <span x-text="stats.system?.db_size_mb ?? '—'"></span>
                <span class="text-sm text-gray-400" x-show="stats.system?.db_size_mb"> MB</span>
            </p>
        </div>
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-microchip mr-1"></i>Memory Usage</p>
            <p class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-1">
                <span x-text="stats.system?.memory_usage_mb ?? '—'"></span>
                <span class="text-sm text-gray-400" x-show="stats.system?.memory_usage_mb"> MB</span>
            </p>
            <p class="text-xs text-gray-400 mt-1" x-show="stats.system?.memory_peak_mb">
                Peak: <span x-text="stats.system?.memory_peak_mb"></span> MB
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-shield-halved mr-1"></i>Backup Status</p>
            <p class="text-lg font-semibold mt-1"
                :class="stats.latest_backup_status === 'completed' ? 'text-green-600' : stats.latest_backup_status === 'failed' ? 'text-red-600' : 'text-amber-600'"
                x-text="stats.latest_backup_status || 'unknown'">-</p>
        </div>
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-code mr-1"></i>PHP / Laravel</p>
            <p class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-1">
                <span x-text="stats.system?.php_version ?? '—'"></span>
                <span class="text-gray-400 text-sm"> / </span>
                <span x-text="stats.system?.laravel_version ?? '—'"></span>
            </p>
        </div>
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-memory mr-1"></i>Memory Limit</p>
            <p class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-1" x-text="stats.system?.memory_limit ?? '—'">-</p>
        </div>
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-user-lock mr-1"></i>Failed Logins Today</p>
            <p class="text-lg font-semibold mt-1"
                :class="(stats.system?.failed_logins_today || 0) > 0 ? 'text-red-600' : 'text-gray-800 dark:text-gray-100'"
                x-text="stats.system?.failed_logins_today ?? 0">-</p>
        </div>
    </div>

    <div class="section-divider"></div>

    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-tachometer-alt"></i>Performance</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-globe mr-1"></i>Requests Today</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1" x-text="stats.system?.requests_today ?? 0">-</p>
        </div>
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-tachometer-alt mr-1"></i>Requests / Minute</p>
            <p class="text-2xl font-bold mt-1" x-text="stats.system?.requests_last_minute ?? 0"
                :class="(stats.system?.requests_last_minute || 0) > 20 ? 'text-red-600' : 'text-gray-800 dark:text-gray-100'">-</p>
        </div>
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-stopwatch mr-1"></i>Avg Response Time</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">
                <span x-text="stats.system?.avg_response_time_ms ?? '—'"></span>
                <span class="text-sm text-gray-400" x-show="stats.system?.avg_response_time_ms"> ms</span>
            </p>
        </div>
        <div class="card-mantis p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-hourglass-half mr-1"></i>Slow Queries Today</p>
            <p class="text-2xl font-bold mt-1"
                :class="(stats.system?.slow_queries_today || 0) > 0 ? 'text-red-600' : 'text-gray-800 dark:text-gray-100'"
                x-text="stats.system?.slow_queries_today ?? 0">-</p>
        </div>
    </div>

    <div x-show="stats.system?.storage_warning" class="card-mantis p-4 bg-red-50 border-red-200 mt-4">
        <p class="text-sm font-medium text-red-700"><i class="fas fa-exclamation-triangle mr-2"></i>Storage running low — less than 15% free</p>
    </div>

    <p class="text-xs text-gray-400 text-right mt-4" x-show="lastUpdated">
        <i class="fas fa-sync-alt mr-1"></i>Last updated: <span x-text="lastUpdated?.toLocaleTimeString()"></span>
    </p>
</div>
@endsection
