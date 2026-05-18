@extends('layouts.admin')

@section('title', 'Notifications - Admin Dashboard')

@section('header', 'Notifications')

@section('content')
<div class="p-8">
    <!-- Notifications Section -->
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-bell"></i>All Notifications</h2>
        <div class="flex items-center space-x-3">
            @if($unreadCount > 0)
                <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-[#74c365] hover:text-[#5dad4f] font-medium text-sm">
                        <i class="fas fa-check-double mr-1"></i>Mark All as Read
                    </button>
                </form>
            @endif
            <form action="{{ route('admin.notifications.clear-all') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to clear all notifications?');">
                @csrf
                <button type="submit" class="text-red-500 hover:text-red-600 font-medium text-sm">
                    <i class="fas fa-trash mr-1"></i>Clear All
                </button>
            </form>
        </div>
    </div>

                <div class="card-mantis p-6">
                    @forelse($notifications as $notification)
                        <div class="notification-item {{ $notification->read_at ? '' : 'unread' }} p-4 rounded-xl mb-4 border border-gray-100">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        @if($notification->read_at)
                                            <i class="fas fa-envelope-open text-gray-400 mr-2"></i>
                                        @else
                                            <i class="fas fa-envelope text-[#74c365] mr-2"></i>
                                        @endif
                                    <h3 class="font-semibold text-gray-800">{{ $notification->title ?? 'Notification' }}</h3>
                                        <span class="ml-3 text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-600 text-sm mb-2">{{ $notification->message ?? '' }}</p>
                                    @if($notification->link)
                                        <a href="{{ route('admin.notifications.open', $notification->id) }}" class="text-[#74c365] text-sm font-medium hover:underline">
                                            <i class="fas fa-external-link-alt mr-1"></i>View Details
                                        </a>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-2 ml-4">
                                    @if(!$notification->read_at)
                                        <form action="{{ route('admin.notifications.mark-read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-gray-500 hover:text-[#74c365] transition-colors p-2" title="Mark as Read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-500 hover:text-red-500 transition-colors p-2" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="fas fa-bell-slash text-gray-300 text-6xl mb-4"></i>
                            <p class="text-gray-500 text-lg">No notifications yet</p>
                            <p class="text-gray-400 text-sm">You'll see notifications here when there are updates</p>
                        </div>
                    @endforelse

                    @if($notifications->hasPages())
                        <div class="mt-6 flex justify-center">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
@endsection
