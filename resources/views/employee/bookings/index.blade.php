@extends('layouts.employee')

@section('title', 'Assigned Work - RGV Multi-Tech Services')

@section('header', 'Assigned Work Requests')

@section('content')
<div class="p-8">
    <div class="card-mantis overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date / Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-[#2563eb]">{{ $booking->reference_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-800">{{ $booking->full_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                            {{ $booking->preferred_date->format('M d, Y') }} at {{ $booking->preferred_time }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
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
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('employee.bookings.show', $booking) }}" class="text-[#2563eb] hover:text-[#1d4ed8] font-medium">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-clipboard-list text-gray-300 text-4xl mb-3 block"></i>
                            No assigned work yet
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $bookings->links() }}</div>
        @endif
    </div>
</div>
@endsection
