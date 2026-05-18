@extends('layouts.employee')

@section('title', 'Assigned Work - RGV Multi-Tech Services')

@section('header', 'Assigned Work Details')

@section('content')
<div class="p-8">
    <div class="mb-8">
        <a href="{{ route('employee.dashboard') }}" class="text-gray-600 hover:text-[#74c365] transition-colors flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
        </a>
    </div>

    <div class="card-mantis p-6 mb-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $booking->full_name }}</h1>
                <p class="text-sm text-gray-500 mt-1">Reference: {{ $booking->reference_number }}</p>
            </div>
            @if($booking->status == 'pending')
                <span class="badge-mantis-warning">Pending</span>
            @elseif($booking->status == 'approved')
                <span class="badge-mantis-success">Approved</span>
            @elseif($booking->status == 'completed')
                <span class="badge-mantis-success">Completed</span>
            @elseif($booking->status == 'rejected')
                <span class="badge-mantis-danger">Rejected</span>
            @elseif($booking->status == 'cancelled')
                <span class="badge-mantis-danger">Cancelled</span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Contact</h3>
                <p class="text-gray-600">{{ $booking->email }}</p>
                <p class="text-gray-600">{{ $booking->contact_number }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Schedule</h3>
                <p class="text-gray-600">{{ $booking->preferred_date->format('F d, Y') }}</p>
                <p class="text-gray-600">{{ $booking->preferred_time }}</p>
            </div>
            <div class="md:col-span-2">
                <h3 class="font-semibold text-gray-700 mb-2">Work Category</h3>
                <p class="text-gray-600">{{ $booking->work_category ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <h3 class="font-semibold text-gray-700 mb-2">Address</h3>
                <p class="text-gray-600">{{ $booking->address }}</p>
            </div>
            @if($booking->reason)
            <div class="md:col-span-2">
                <h3 class="font-semibold text-gray-700 mb-2">Purpose / Reason</h3>
                <p class="text-gray-600">{{ $booking->reason }}</p>
            </div>
            @endif
            @if($booking->remarks)
            <div class="md:col-span-2">
                <h3 class="font-semibold text-gray-700 mb-2">Remarks</h3>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-gray-700">{{ $booking->remarks }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
