@extends('layouts.employee')

@section('title', 'Assigned Work - RGV Multi-Tech Services')

@section('header', 'Assigned Work Requests')

@section('content')
<div class="p-4 md:p-8">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-calendar-check"></i>Assigned Work Requests</h2>
        <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $bookings->total() }} requests</span>
    </div>
    <div class="card-mantis overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
            <thead class="bg-gradient-to-r from-[#eff6ff] to-white dark:from-gray-800 dark:to-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Reference</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Date &amp; Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                            <span class="font-mono text-xs font-semibold text-[#2563eb]">{{ $booking->reference_number }}</span>
                        </td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $booking->full_name }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                            <p class="text-xs text-gray-800 dark:text-gray-100">{{ $booking->preferred_date->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $booking->preferred_time_formatted }}</p>
                        </td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                            @if($booking->status == 'pending')
                                <span class="badge-mantis-warning">Pending</span>
                            @elseif($booking->status == 'approved')
                                <span class="badge-mantis-success">Approved</span>
                            @elseif($booking->status == 'completed')
                                <span class="badge-mantis-success">Completed</span>
                            @else
                                <span class="badge-mantis-danger">{{ ucfirst($booking->status) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                            <a href="{{ route('employee.bookings.show', $booking) }}" class="w-9 h-9 bg-[#eff6ff] text-[#2563eb] rounded-lg inline-flex items-center justify-center hover:bg-[#dbeafe] transition-colors" title="View details" aria-label="View work request {{ $booking->reference_number }}">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="mb-3 flex justify-center">
                                <i class="fas fa-clipboard-list text-gray-300 text-4xl" aria-hidden="true"></i>
                            </div>
                            No assigned work yet
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">{{ $bookings->links() }}</div>
        @endif
    </div>
</div>
@endsection
