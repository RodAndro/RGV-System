@extends('layouts.employee')

@section('title', 'Notifications - RGV Multi-Tech Services')

@section('header', 'Notifications')

@section('content')
<div class="p-4 md:p-8">
    @if(session('success'))
        <div class="bg-gradient-to-r from-[#eff6ff] to-[#dbeafe] dark:from-blue-900/20 dark:to-blue-800/10 border border-[#2563eb] dark:border-blue-700 text-[#1e40af] dark:text-blue-300 px-4 py-3 rounded-xl mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-[#eff6ff] dark:bg-blue-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-bell text-[#2563eb]"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Total Notifications</p>
                <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $notifications->total() }}</p>
            </div>
        </div>
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-yellow-50 dark:bg-yellow-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-envelope text-yellow-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Unread</p>
                <p class="text-lg font-bold text-yellow-600">{{ $unreadCount }}</p>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-list"></i>All Notifications</h2>
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $notifications->count() }} of {{ $notifications->total() }}</span>
            @if($unreadCount > 0)
                <form action="{{ route('employee.notifications.mark-all-read') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-[#2563eb] hover:text-[#1d4ed8] font-semibold transition-colors">
                        <i class="fas fa-check-double mr-1"></i>Mark All Read
                    </button>
                </form>
            @endif
            <form action="{{ route('employee.notifications.clear-all') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to clear all notifications?');">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-semibold transition-colors">
                    <i class="fas fa-trash mr-1"></i>Clear All
                </button>
            </form>
        </div>
    </div>

    <div class="card-mantis overflow-hidden">
        @forelse($notifications as $notification)
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 dark:border-gray-700 px-4 py-4 last:border-b-0 {{ $notification->read_at ? '' : 'border-l-4 border-l-[#2563eb] bg-[#eff6ff]/70 dark:bg-blue-900/20' }}">
                <div class="flex min-w-0 items-start gap-3">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg {{ $notification->read_at ? 'bg-gray-100 dark:bg-gray-800 text-gray-500' : 'bg-[#eff6ff] dark:bg-blue-900/30 text-[#2563eb]' }}">
                        <i class="fas {{ $notification->read_at ? 'fa-envelope-open' : 'fa-envelope' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                            <h3 class="font-semibold text-gray-800 dark:text-gray-100">{{ $notification->title ?? 'Notification' }}</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $notification->message ?? '' }}</p>
                        @if($notification->link)
                            <a href="{{ route('employee.notifications.open', $notification->id) }}" class="inline-flex items-center mt-2 text-[#2563eb] text-sm font-semibold hover:underline">
                                <i class="fas fa-external-link-alt mr-1"></i>View Details
                            </a>
                        @endif
                    </div>
                </div>
                <div class="flex flex-shrink-0 items-center gap-2">
                    @if(!$notification->read_at)
                        <form action="{{ route('employee.notifications.mark-read', $notification->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="w-9 h-9 rounded-lg bg-[#eff6ff] text-[#2563eb] hover:bg-[#dbeafe] transition-colors" title="Mark as read" aria-label="Mark notification as read">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('employee.notifications.destroy', $notification->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-9 h-9 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors" title="Delete" aria-label="Delete notification">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <i class="fas fa-bell-slash text-gray-400 text-2xl"></i>
                </div>
                <p class="font-medium text-gray-700 dark:text-gray-200">No notifications yet</p>
                <p class="mt-1 text-sm">Updates for your work and borrow requests will appear here.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-4 rounded-lg border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection