@extends('layouts.admin')

@section('title', 'Backups')

@section('content')
<div class="p-6 space-y-6">
    @if(session('error'))
        <div class="rounded bg-red-50 p-4 text-red-700">{{ session('error') }}</div>
    @endif

    <section class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-gray-900">Backups</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.backups.settings') }}" class="rounded bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">
                    <i class="fas fa-cog mr-1"></i>Settings
                </a>
                <form method="POST" action="{{ route('admin.backups.run') }}">
                    @csrf
                    <button class="rounded bg-[#2563eb] px-4 py-2 font-semibold text-white">Run Manual Backup</button>
                </form>
                <form action="{{ route('admin.backups.clear-all') }}" method="POST" onsubmit="return confirm('Delete ALL backup records? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="rounded bg-red-600 px-4 py-2 text-white text-sm font-semibold hover:bg-red-700">
                        <i class="fas fa-trash mr-1"></i>Clear All
                    </button>
                </form>
            </div>
        </div>

        @if(isset($retention))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
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

    <section class="card-mantis overflow-hidden">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-3 border-b border-r border-gray-200">Date</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200">Disk</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200">Status</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200">Size</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200">Checksum</th>
                    <th class="px-4 py-3 border-b border-r border-gray-200">Message</th>
                    <th class="px-4 py-3 border-b border-gray-200">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($backups as $backup)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-xs">{{ $backup->created_at }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-xs">{{ $backup->disk }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100">
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
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-xs">{{ $backup->size_formatted }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-xs">
                            @if($backup->checksum)
                                @php $verifyResult = $backup->verifyChecksum(); @endphp
                                @if($verifyResult === true)
                                    <span class="text-green-600"><i class="fas fa-check-circle"></i> Valid</span>
                                @elseif($verifyResult === false)
                                    <span class="text-red-600"><i class="fas fa-times-circle"></i> Mismatch</span>
                                @else
                                    <span class="text-gray-400">{{ substr($backup->checksum, 0, 8) }}...</span>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-xs">{{ Str::limit($backup->message, 80) }}</td>
                        <td class="px-4 py-3 border-b border-gray-100 text-xs">
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
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $backups->links() }}
        </div>
    </section>
</div>
@endsection
