@extends('layouts.admin')

@section('title', 'Backups')

@section('content')
<div class="p-4 md:p-8 space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4 text-green-700 dark:text-green-300">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4 text-red-700 dark:text-red-300">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4 text-red-700 dark:text-red-300">{{ $errors->first() }}</div>
    @endif

    <section class="card-mantis p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Backups</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.backups.settings') }}" class="btn-mantis-outline px-4 py-2 text-sm">
                    <i class="fas fa-cog mr-1"></i>Settings
                </a>
                <form method="POST" action="{{ route('admin.backups.run') }}">
                    @csrf
                    <button class="btn-mantis px-4 py-2 text-sm"><i class="fas fa-database mr-1"></i>Create Backup</button>
                </form>
                <form method="POST" action="{{ route('admin.backups.export') }}">
                    @csrf
                    <button class="rounded-lg border border-[#2563eb] px-3 py-2 text-xs font-medium text-[#2563eb] hover:bg-[#eff6ff] dark:hover:bg-blue-900/20 transition-colors"><i class="fas fa-file-export mr-1"></i>Export ZIP</button>
                </form>
                <form action="{{ route('admin.backups.clear-all') }}" method="POST" onsubmit="return confirm('Delete ALL backup records? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="rounded-lg bg-red-50 dark:bg-red-900/20 px-3 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
                        <i class="fas fa-trash mr-1"></i>Clear All
                    </button>
                </form>
            </div>
        </div>

        @if(isset($retention))
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-sm text-blue-800 dark:text-blue-300">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Retention Policy:</strong>
            Keep all backups for {{ $retention['keep_all_backups_for_days'] }} day(s),
            daily for {{ $retention['keep_daily_backups_for_days'] }} days,
            weekly for {{ $retention['keep_weekly_backups_for_weeks'] }} weeks,
            monthly for {{ $retention['keep_monthly_backups_for_months'] }} months,
            yearly for {{ $retention['keep_yearly_backups_for_years'] }} years.
            Auto-cleanup runs daily at 3:30 AM.
        </div>
        @endif
    </section>

    <section class="card-mantis p-6">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-upload"></i>Import Backup</h2>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Restore a ZIP containing one SQL file. This replaces the current public database contents.</p>
        <form method="POST" action="{{ route('admin.backups.import') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3" onsubmit="return confirm('Restore this backup and replace current database contents?');">
            @csrf
            <div class="min-w-0 flex-1">
                <label for="backup-file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Backup ZIP</label>
                <input id="backup-file" name="file" type="file" accept=".zip,application/zip" required class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="btn-mantis px-4 py-2 text-sm"><i class="fas fa-upload mr-1"></i>Import and Restore</button>
        </form>
    </section>

    <section class="card-mantis overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-3 border-b border-gray-100 dark:border-gray-700">
            <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $backups->count() }} of {{ $backups->total() }} backups</span>
            <x-per-page-selector />
        </div>
        <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead class="bg-gradient-to-r from-[#eff6ff] to-white dark:from-gray-800 dark:to-gray-900">
                <tr class="text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">
                    <th class="px-4 py-3 border-b border-r border-gray-200 dark:border-gray-700">Date</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200 dark:border-gray-700">Disk</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200 dark:border-gray-700">Status</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200 dark:border-gray-700">Size</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200 dark:border-gray-700">Checksum</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200 dark:border-gray-700">Message</th>
                    <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($backups as $backup)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-700 dark:text-gray-300">{{ $backup->created_at }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-700 dark:text-gray-300">{{ $backup->disk }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                            @if($backup->status === 'success')
                                <span class="badge-mantis-success">Success</span>
                            @elseif($backup->status === 'failed')
                                <span class="badge-mantis-danger">Failed</span>
                            @elseif($backup->status === 'processing' || $backup->status === 'queued')
                                <span class="badge-mantis-warning">{{ ucfirst($backup->status) }}</span>
                            @else
                                {{ $backup->status }}
                            @endif
                        </td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-700 dark:text-gray-300">{{ $backup->size_formatted }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs">
                            @if($backup->checksum)
                                @php $verifyResult = $backup->verifyChecksum(); @endphp
                                @if($verifyResult === true)
                                    <span class="text-green-600 dark:text-green-400"><i class="fas fa-check-circle"></i> Valid</span>
                                @elseif($verifyResult === false)
                                    <span class="text-red-600 dark:text-red-400"><i class="fas fa-times-circle"></i> Mismatch</span>
                                @else
                                    <span class="text-gray-400">{{ substr($backup->checksum, 0, 8) }}...</span>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td title="{{ $backup->message }}" class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-700 dark:text-gray-300">{{ Str::limit($backup->message, 140) }}</td>
                        <td class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 text-xs">
                            @if($backup->checksum && $backup->file_path)
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.backups.download', $backup) }}" class="text-[#2563eb] hover:text-[#1d4ed8] font-medium">
                                        <i class="fas fa-download mr-1"></i>Download
                                    </a>
                                    <form method="POST" action="{{ route('admin.backups.verify', $backup) }}" class="inline">
                                        @csrf
                                        <button class="text-[#1e40af] hover:underline">
                                            <i class="fas fa-shield-halved mr-1"></i>Verify
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No backup records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $backups->links() }}
        </div>
    </section>
</div>
@endsection
