@extends('layouts.admin')

@section('title', 'Manage Work Request - RGV Multi-Tech Services')

@section('header', 'Work Request Management')

@section('content')
<div class="p-8">
                <!-- Summary Stats Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 mb-8">
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-blue-50 dark:bg-blue-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-calendar text-blue-500"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Total</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-yellow-50 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-yellow-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Pending</p>
                            <p class="text-lg font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                        </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-blue-50 dark:bg-blue-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check-circle text-blue-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Approved</p>
                            <p class="text-lg font-bold text-blue-600">{{ $stats['approved'] }}</p>
                        </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-green-50 dark:bg-green-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check-double text-green-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Completed</p>
                            <p class="text-lg font-bold text-green-600">{{ $stats['completed'] }}</p>
                        </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-red-50 dark:bg-red-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-times-circle text-red-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Rejected</p>
                            <p class="text-lg font-bold text-red-600">{{ $stats['rejected'] }}</p>
                        </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-gray-50 dark:bg-gray-800 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-ban text-gray-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Cancelled</p>
                            <p class="text-lg font-bold text-gray-600">{{ $stats['cancelled'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4"></div>

                <!-- Filters Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-filter"></i>Filter</h2>
                </div>
                <div class="card-mantis p-4 mb-6">
                    <form action="{{ route('admin.bookings.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <select name="status" class="pl-3 pr-8 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <input type="text" name="search" placeholder="Search by name or reference..." value="{{ request('search') }}" class="flex-1 px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                        <button type="submit" class="btn-mantis px-4 py-2 text-sm">
                            <i class="fas fa-search mr-1"></i>Search
                        </button>
                        @if(request()->hasAny('status', 'search'))
                            <a href="{{ route('admin.bookings.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm">
                                <i class="fas fa-times mr-1"></i>Clear
                            </a>
                        @endif
                    </form>
                </div>

                <div class="section-divider"></div>
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-list"></i>All Request</h2>
                    <div class="flex items-center gap-2">
                        <x-per-page-selector />
                        <a href="{{ route('admin.import-export.bookings.export', request()->query() + ['format' => 'xlsx']) }}" class="px-3 py-1.5 border border-[#2563eb] rounded-lg text-xs text-[#2563eb] hover:bg-[#eff6ff] dark:text-[#2563eb] dark:border-[#2563eb] dark:hover:bg-blue-900/20 transition-colors">
                            <i class="fas fa-download mr-1"></i>Export
                        </a>
                    </div>
                </div>
                <div class="card-mantis overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-gradient-to-r from-[#eff6ff] to-white dark:from-gray-800 dark:to-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Reference</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Customer</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Contact</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Date & Time</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Assigned To</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bookings as $booking)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                            <span class="font-mono text-xs font-semibold text-[#2563eb]">{{ $booking->reference_number }}</span>
                                        </td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                            <p class="font-semibold text-sm text-gray-800 dark:text-gray-100">{{ $booking->full_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $booking->email }}</p>
                                        </td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">{{ $booking->contact_number }}</td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                            <p class="text-xs text-gray-800 dark:text-gray-100">{{ $booking->preferred_date->format('M d, Y') }}</p>
                                            <p class="text-xs text-gray-500">{{ $booking->preferred_time }}</p>
                                        </td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                            @if($booking->status == 'pending')
                                                <span class="badge-mantis-warning">{{ ucfirst($booking->status) }}</span>
                                            @elseif($booking->status == 'approved')
                                                <span class="badge-mantis-success">{{ ucfirst($booking->status) }}</span>
                                            @elseif($booking->status == 'rejected')
                                                <span class="badge-mantis-danger">{{ ucfirst($booking->status) }}</span>
                                            @elseif($booking->status == 'completed')
                                                <span class="badge-mantis-success">{{ ucfirst($booking->status) }}</span>
                                            @elseif($booking->status == 'cancelled')
                                                <span class="badge-mantis-danger">{{ ucfirst($booking->status) }}</span>
                                            @else
                                                <span class="badge-mantis-info">{{ ucfirst($booking->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">
                                            {{ $booking->employee ? $booking->employee->name : 'Unassigned' }}
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.bookings.show', $booking) }}" class="w-9 h-9 bg-[#eff6ff] text-[#2563eb] rounded-lg flex items-center justify-center hover:bg-[#dbeafe] transition-colors" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($booking->status == 'pending')
                                                    <form action="{{ route('admin.bookings.approve', $booking) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="w-9 h-9 bg-[#eff6ff] text-[#2563eb] rounded-lg flex items-center justify-center hover:bg-[#dbeafe] transition-colors" title="Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if(in_array($booking->status, ['pending', 'approved']))
                                                    <a href="#" onclick="showRejectModal({{ $booking->id }})" class="w-9 h-9 bg-red-100 text-red-600 rounded-lg flex items-center justify-center hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            <div class="w-16 h-16 bg-[#eff6ff] rounded-full flex items-center justify-center mx-auto mb-4">
                                                <i class="fas fa-calendar-times text-[#2563eb] text-2xl"></i>
                                            </div>
                                            <p class="font-medium">No work request found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($bookings->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100">
                            {{ $bookings->links() }}
                        </div>
                    @endif
                </div>
</div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 backdrop-blur-sm">
        <div class="card-mantis p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Reject Work Request</h3>
            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" name="booking_id" id="rejectBookingId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection *</label>
                    <textarea name="remarks" rows="4" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                        placeholder="Please provide a reason for rejecting this work request"></textarea>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="hideRejectModal()" class="px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-100 transition-all font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all font-medium">
                        Reject Work Request
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function showRejectModal(bookingId) {
    document.getElementById('rejectBookingId').value = bookingId;
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
}

function hideRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
}

document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const bookingId = document.getElementById('rejectBookingId').value;
    const formData = new FormData(this);
    
    fetch(`{{ route('admin.bookings.reject', ':id') }}`.replace(':id', bookingId), {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    }).then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
});
</script>
@endpush
