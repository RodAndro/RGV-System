@extends('layouts.admin')

@section('title', 'Bookings Report - Admin Dashboard')

@section('header', 'Bookings Report')

@section('content')
<div class="p-4 md:p-8">
    <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center text-gray-600 hover:text-[#2563eb] transition-colors text-sm mb-3">
        <i class="fas fa-arrow-left mr-1"></i>Back to Reports
    </a>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 mb-4">
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-blue-50 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar text-blue-500"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Total</p>
                <p class="text-lg font-bold text-gray-800">{{ $stats['total'] }}</p>
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
            <div class="bg-blue-50 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check-circle text-blue-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Approved</p>
                <p class="text-lg font-bold text-blue-600">{{ $stats['approved'] }}</p>
            </div>
        </div>
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-green-50 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check-double text-green-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Completed</p>
                <p class="text-lg font-bold text-green-600">{{ $stats['completed'] }}</p>
            </div>
        </div>
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-red-50 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-times-circle text-red-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Rejected</p>
                <p class="text-lg font-bold text-red-600">{{ $stats['rejected'] ?? 0 }}</p>
            </div>
        </div>
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-gray-50 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-ban text-gray-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Cancelled</p>
                <p class="text-lg font-bold text-gray-600">{{ $stats['cancelled'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-filter"></i>Filter</h2>
    </div>
    <div class="card-mantis p-4 mb-6">
        <form method="GET" action="{{ route('admin.reports.bookings') }}">
            <div class="flex flex-wrap items-center gap-2">
                <select name="status" class="pl-3 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search reference or customer..." class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
                <button type="submit" class="btn-mantis px-4 py-2 text-sm">
                    <i class="fas fa-search mr-1"></i>Search
                </button>
                <a href="{{ route('admin.reports.bookings') }}" class="btn-mantis-outline px-4 py-2 text-sm">Clear</a>
            </div>
        </form>
    </div>

    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-list"></i>All Bookings Data</h2>
        <div class="flex items-center gap-2">
            <x-per-page-selector />
        </div>
    </div>

    <div class="card-mantis overflow-hidden">
        <table class="w-full border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Reference</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-gray-200">Employee</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm font-medium">{{ $booking->reference_number }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm">{{ $booking->full_name }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm">{{ $booking->preferred_date->format('M d, Y') }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm">{{ ucfirst(str_replace('-', ' ', $booking->purpose_category)) }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm">
                            <span class="badge-mantis-{{ $booking->status == 'pending' ? 'warning' : ($booking->status == 'approved' ? 'success' : ($booking->status == 'completed' ? 'success' : ($booking->status == 'rejected' ? 'danger' : 'warning'))) }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 border-b border-gray-100 text-sm">{{ $booking->employee ? $booking->employee->name : 'Unassigned' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No bookings found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<x-chatbot pageType="bookings" />
@endpush
