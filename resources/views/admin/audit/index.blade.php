@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
<div class="p-6 space-y-6">
    <section class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Audit Logs</h1>
            <div class="flex gap-2">
                <a class="rounded bg-gray-900 px-4 py-2 text-white" href="{{ route('admin.audit.export', request()->query() + ['format' => 'csv']) }}">CSV</a>
                <a class="rounded bg-gray-900 px-4 py-2 text-white" href="{{ route('admin.audit.export', request()->query() + ['format' => 'pdf']) }}">PDF</a>
                <form action="{{ route('admin.audit.clear-all') }}" method="POST" onsubmit="return confirm('Delete ALL audit logs? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="rounded bg-red-600 px-4 py-2 text-white text-sm font-semibold hover:bg-red-700">
                        <i class="fas fa-trash mr-1"></i>Clear All
                    </button>
                </form>
            </div>
        </div>

        <div class="section-header mt-4">
            <h2 class="section-title"><i class="fas fa-filter"></i>Filter Audit Logs</h2>
        </div>

        <form method="GET" action="{{ route('admin.audit.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event</label>
                <input name="event" value="{{ request('event') }}" placeholder="Search events..." class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-300 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-white dark:bg-white dark:text-gray-900">
            </div>
            <div class="min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                <input name="date_from" value="{{ request('date_from') }}" type="date" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-300 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-white dark:bg-white dark:text-gray-900">
            </div>
            <div class="min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                <input name="date_to" value="{{ request('date_to') }}" type="date" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-300 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-white dark:bg-white dark:text-gray-900">
            </div>
            <button type="submit" class="btn-mantis px-6">
                <i class="fas fa-search mr-2"></i>Search
            </button>
            <a href="{{ route('admin.audit.index') }}" class="btn-mantis-outline px-6">
                Clear
            </a>
        </form>
    </section>

    <section class="bg-white dark:bg-gray-900 rounded-lg shadow p-6 overflow-x-auto">
        <div class="flex items-center justify-between mb-4">
            <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $logs->total() }} logs</span>
            <x-per-page-selector />
        </div>
        <table class="min-w-full text-sm">
            <thead><tr class="text-left text-gray-500 dark:text-gray-400"><th>Date</th><th>Event</th><th>User</th><th>Subject</th><th>IP</th><th>Checksum</th><th>Flags</th></tr></thead>
            <tbody>
                @foreach($logs as $log)
                    <tr class="border-t dark:border-gray-700 {{ $log->event === 'failed login' ? 'bg-red-50 dark:bg-red-900/20' : ($log->event === 'error' ? 'bg-amber-50 dark:bg-amber-900/20' : '') }}">
                        <td class="py-2 dark:text-gray-100">{{ $log->created_at }}</td>
                        <td>
                            @if($log->event === 'failed login')
                                <span class="text-red-600 font-medium"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $log->event }}</span>
                            @elseif($log->event === 'error')
                                <span class="text-amber-600 font-medium"><i class="fas fa-bug mr-1"></i>{{ $log->event }}</span>
                            @else
                                <span class="dark:text-gray-100">{{ $log->event }}</span>
                            @endif
                        </td>
                        <td class="dark:text-gray-100">{{ $log->user?->email ?? 'System' }}</td>
                        <td class="dark:text-gray-100">{{ class_basename($log->auditable_type ?? '') }} #{{ $log->auditable_id }}</td>
                        <td class="dark:text-gray-100">{{ $log->ip_address }}</td>
                        <td class="dark:text-gray-100">{{ $log->isChecksumValid() ? 'Valid' : 'Invalid' }}</td>
                        <td class="py-2">
                            <div class="flex flex-wrap gap-1">
                                @if(!$log->isChecksumValid())
                                    <span class="badge-mantis-danger animate-pulse"><i class="fas fa-shield-halved mr-1"></i>TAMPERED</span>
                                @endif
                                @if(in_array($log->ip_address, $bruteForceIps))
                                    <span class="badge-mantis-warning"><i class="fas fa-user-lock mr-1"></i>Brute Force</span>
                                @endif
                                @if(in_array($log->ip_address, $rapidFireIps) || ($log->user_id && in_array($log->user_id, $rapidFireUsers)))
                                    <span class="badge-mantis-warning"><i class="fas fa-bolt mr-1"></i>Burst</span>
                                @endif
                                @if($log->created_at && $log->created_at->hour >= 0 && $log->created_at->hour < 5)
                                    <span class="text-gray-400 dark:text-gray-300" title="Unusual hour ({{ $log->created_at->format('H:i') }})"><i class="fas fa-moon"></i></span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $logs->links() }}
    </section>
</div>
@endsection
