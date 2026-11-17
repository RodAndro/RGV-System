@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
<div class="p-6 space-y-6">
    <section class="card-mantis p-6">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h1 class="text-2xl font-semibold text-gray-800">Audit Logs</h1>
            <div class="flex gap-2">
                <a class="px-3 py-2 border border-[#2563eb] rounded-lg text-xs text-[#2563eb] hover:bg-[#eff6ff] transition-colors font-medium" href="{{ route('admin.audit.export', request()->query() + ['format' => 'csv']) }}">
                    <i class="fas fa-file-csv mr-1"></i>CSV
                </a>
                <a class="px-3 py-2 border border-[#2563eb] rounded-lg text-xs text-[#2563eb] hover:bg-[#eff6ff] transition-colors font-medium" href="{{ route('admin.audit.export', request()->query() + ['format' => 'pdf']) }}">
                    <i class="fas fa-file-pdf mr-1"></i>PDF
                </a>
                <button type="button" @click="$dispatch('open-modal', 'clear-audit-logs')" class="px-3 py-2 bg-red-50 text-red-600 rounded-lg text-xs font-medium hover:bg-red-100 transition-colors">
                    <i class="fas fa-trash mr-1"></i>Clear All
                </button>
            </div>
        </div>

        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-filter"></i>Filter</h2>
        </div>

        <form method="GET" action="{{ route('admin.audit.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
            <input name="event" value="{{ request('event') }}" placeholder="Search events..." class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
            <input name="date_from" value="{{ request('date_from') }}" type="date" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
            <input name="date_to" value="{{ request('date_to') }}" type="date" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
            <button type="submit" class="btn-mantis px-4 py-2 text-sm">
                <i class="fas fa-search mr-1"></i>Search
            </button>
            <a href="{{ route('admin.audit.index') }}" class="btn-mantis-outline px-4 py-2 text-sm">
                Clear
            </a>
        </form>
    </section>

    <x-modal name="clear-audit-logs" maxWidth="md" :show="$errors->has('password')">
        <form action="{{ route('admin.audit.clear-all') }}" method="POST" class="p-6">
            @csrf

            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Confirm Audit Log Deletion</h3>
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                This will permanently clear all audit logs and cannot be undone. Enter your password to continue.
            </p>

            <div class="mb-4">
                <x-input-label for="clear-audit-password" value="Password" />
                <x-text-input id="clear-audit-password" type="password" name="password" class="mt-1 block w-full" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal', 'clear-audit-logs')" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors text-sm font-medium">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                    <i class="fas fa-trash mr-2"></i>Clear All Logs
                </button>
            </div>
        </form>
    </x-modal>

    <section class="card-mantis overflow-hidden">
        <div class="flex items-center justify-end px-6 py-3 border-b border-gray-100">
            <x-per-page-selector />
        </div>
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-3 border-b border-r border-gray-200">Date</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200">Event</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200">User</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200">Subject</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200">IP</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200">Checksum</th>
                    <th class="px-4 py-3 border-b border-gray-200">Flags</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors {{ $log->event === 'failed login' ? 'bg-red-50' : ($log->event === 'error' ? 'bg-amber-50' : '') }}">
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-xs">{{ $log->created_at }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100">
                            @if($log->event === 'failed login')
                                <span class="text-red-600 font-medium text-xs"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $log->event }}</span>
                            @elseif($log->event === 'error')
                                <span class="text-amber-600 font-medium text-xs"><i class="fas fa-bug mr-1"></i>{{ $log->event }}</span>
                            @else
                                <span class="text-xs">{{ $log->event }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-xs">{{ $log->user?->email ?? 'System' }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-xs">{{ class_basename($log->auditable_type ?? '') }} #{{ $log->auditable_id }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-xs">{{ $log->ip_address }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-xs">{{ $log->isChecksumValid() ? 'Valid' : 'Invalid' }}</td>
                        <td class="px-4 py-3 border-b border-gray-100 text-xs">
                            <div class="flex flex-wrap gap-1">
                                @if(!$log->isChecksumValid())
                                    <span class="badge-mantis-danger"><i class="fas fa-shield-halved mr-1"></i>TAMPERED</span>
                                @endif
                                @if(in_array($log->ip_address, $bruteForceIps))
                                    <span class="badge-mantis-warning"><i class="fas fa-user-lock mr-1"></i>Brute Force</span>
                                @endif
                                @if(in_array($log->ip_address, $rapidFireIps) || ($log->user_id && in_array($log->user_id, $rapidFireUsers)))
                                    <span class="badge-mantis-warning"><i class="fas fa-bolt mr-1"></i>Burst</span>
                                @endif
                                @if($log->created_at && $log->created_at->hour >= 0 && $log->created_at->hour < 5)
                                    <span class="text-gray-400" title="Unusual hour ({{ $log->created_at->format('H:i') }})"><i class="fas fa-moon"></i></span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
    </section>
</div>
@endsection
