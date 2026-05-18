@extends('layouts.admin')

@section('title', 'Bookings Report - Admin Dashboard')

@section('header', 'Bookings Report')

@section('content')
<div class="p-8">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('admin.reports.index') }}" class="text-gray-600 hover:text-[#74c365] transition-colors flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Back to Reports
        </a>
        <button onclick="saveReportFilter()" class="rounded bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
            <i class="fas fa-bookmark mr-1"></i>Save Filter
        </button>
    </div>

    <script>
        function saveReportFilter() {
            const name = prompt('Name this filter:');
            if (!name) return;
            const saved = JSON.parse(localStorage.getItem('savedReportFilters') || '[]');
            saved.push({ name: name, url: window.location.href });
            localStorage.setItem('savedReportFilters', JSON.stringify(saved));
            alert('Filter saved as "' + name + '".');
        }
    </script>

    <!-- Filters Section -->
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-filter"></i>Filter Bookings</h2>
    </div>
    <div class="card-mantis p-6 mb-8">
        <form method="GET" action="{{ route('admin.reports.bookings') }}">
            <div class="flex flex-wrap gap-4">
                <select name="status" class="px-4 py-2.5 pr-10 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search reference or customer..." class="flex-1 px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400">
                <button type="submit" class="btn-mantis px-6">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="{{ route('admin.reports.bookings') }}" class="btn-mantis-outline px-6">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Bookings</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-blue-50 w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                </div>
                <div class="bg-yellow-50 w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Approved</p>
                    <p class="text-3xl font-bold text-[#74c365]">{{ $stats['approved'] }}</p>
                </div>
                <div class="bg-[#f0f9ef] w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-[#74c365] text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Completed</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['completed'] }}</p>
                </div>
                <div class="bg-blue-50 w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-double text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    <!-- Bookings Table Section -->
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-list"></i>All Bookings Data</h2>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $bookings->total() }} records</span>
            <x-per-page-selector />
        </div>
    </div>

    <div class="card-mantis overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($bookings as $booking)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $booking->reference_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $booking->full_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $booking->preferred_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst(str_replace('-', ' ', $booking->purpose_category)) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge-mantis-{{ $booking->status == 'pending' ? 'warning' : ($booking->status == 'approved' ? 'success' : ($booking->status == 'completed' ? 'success' : ($booking->status == 'rejected' ? 'danger' : 'warning'))) }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $booking->employee ? $booking->employee->name : 'Unassigned' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No bookings found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<x-chatbot pageType="bookings" />
@endpush
