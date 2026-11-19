@extends('layouts.admin')

@section('title', 'Booking Details - Admin Dashboard')

@section('header', 'Booking Details')

@section('content')
<div class="p-4 md:p-8">
    <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center text-gray-600 hover:text-[#2563eb] transition-colors text-sm mb-3">
        <i class="fas fa-arrow-left mr-1"></i>Back to Work Request
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="card-mantis p-6 mb-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Reference Number</p>
                        <p class="font-mono text-2xl font-bold text-[#2563eb]">{{ $booking->reference_number }}</p>
                    </div>
                    <span class="badge-mantis-{{ $booking->status == 'pending' ? 'warning' : ($booking->status == 'approved' ? 'success' : ($booking->status == 'rejected' ? 'danger' : ($booking->status == 'completed' ? 'success' : 'warning'))) }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <div><p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Full Name</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $booking->full_name }}</p></div>
                    <div><p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Email</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $booking->email }}</p></div>
                    <div><p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Contact Number</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $booking->contact_number }}</p></div>
                    <div><p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Purpose Category</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ ucfirst(str_replace('-', ' ', $booking->purpose_category)) }}</p></div>
                    <div><p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Preferred Date</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $booking->preferred_date->format('F d, Y') }}</p></div>
                    <div><p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Preferred Time</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $booking->preferred_time_formatted }}</p></div>
                    <div class="md:col-span-2"><p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Address</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $booking->address }}</p></div>
                    <div class="md:col-span-2"><p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Reason</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $booking->reason }}</p></div>
                </div>
                @if($booking->remarks)
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Remarks</p>
                        <p class="text-gray-800 dark:text-gray-100">{{ $booking->remarks }}</p>
                    </div>
                @endif
            </div>

            <div class="card-mantis p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-6">Booking Timeline</h3>
                <div class="relative">
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                    <div class="relative pl-10 pb-6">
                        <div class="absolute left-2 w-5 h-5 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-full border-4 border-white dark:border-gray-800 shadow-lg shadow-blue-500/30"></div>
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-gray-100">Booking Submitted</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $booking->created_at->format('F d, Y - g:i A') }}</p>
                        </div>
                    </div>
                    @if($booking->approved_at)
                    <div class="relative pl-10 pb-6">
                        <div class="absolute left-2 w-5 h-5 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-full border-4 border-white dark:border-gray-800 shadow-lg shadow-blue-500/30"></div>
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-gray-100">Booking Approved</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $booking->approved_at->format('F d, Y - g:i A') }}</p>
                        </div>
                    </div>
                    @endif
                    @if($booking->completed_at)
                    <div class="relative pl-10 pb-6">
                        <div class="absolute left-2 w-5 h-5 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-full border-4 border-white dark:border-gray-800 shadow-lg shadow-blue-500/30"></div>
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-gray-100">Booking Completed</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $booking->completed_at->format('F d, Y - g:i A') }}</p>
                        </div>
                    </div>
                    @endif
                    @if($booking->cancelled_at)
                    <div class="relative pl-10">
                        <div class="absolute left-2 w-5 h-5 bg-gray-500 rounded-full border-4 border-white dark:border-gray-800 shadow-lg shadow-gray-500/30"></div>
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-gray-100">Booking Cancelled</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $booking->cancelled_at->format('F d, Y - g:i A') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="card-mantis p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Actions</h3>
                @if($booking->status == 'pending')
                    <button type="button" @click="$dispatch('open-modal', 'approve-work-request')" class="w-full btn-mantis justify-center mb-4"><i class="fas fa-check mr-2"></i>Approve Work Request</button>
                    <button type="button" @click="$dispatch('open-modal', 'reject-work-request')" class="w-full px-6 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium mb-4">
                        <i class="fas fa-times mr-2"></i>Reject Work Request
                    </button>
                @endif
                @if($booking->status == 'approved')
                    <button type="button" @click="$dispatch('open-modal', 'complete-work-request')" class="w-full btn-mantis inline-flex items-center justify-center mb-4"><i class="fas fa-check-circle mr-2"></i>Mark as Completed</button>
                @endif
                @if(in_array($booking->status, ['pending', 'approved']))
                    <button type="button" @click="$dispatch('open-modal', 'cancel-work-request')" class="w-full px-6 py-3 bg-gray-700 dark:bg-gray-700 text-white rounded-xl hover:bg-gray-800 dark:hover:bg-gray-600 transition-all font-medium mb-4">
                        <i class="fas fa-ban mr-2"></i>Cancel Booking
                    </button>
                @endif
                @if(in_array($booking->status, ['completed', 'rejected'], true))
                    <button type="button" @click="$dispatch('open-modal', 'trash-work-request')" class="w-full px-6 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium inline-flex items-center justify-center">
                        <i class="fas fa-trash-alt mr-2"></i>Move to Trash
                    </button>
                @endif
            </div>

            <div class="card-mantis p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Assign Employee</h3>
                <form action="{{ route('admin.bookings.assign', $booking) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Employee</label>
                        <select name="employee_id" required class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">Unassigned</option>
                            @foreach(App\Models\User::role('employee')->get() as $employee)
                                <option value="{{ $employee->id }}" {{ $booking->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full btn-mantis inline-flex items-center justify-center"><i class="fas fa-user-plus mr-2"></i>Assign Employee</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if($booking->status == 'pending')
    <x-confirm-action-modal name="approve-work-request" action="{{ route('admin.bookings.approve', $booking) }}" title="Confirm Approval" message="Approve work request {{ $booking->reference_number }}?" confirmText="Approve Request" icon="fa-check" confirmClass="bg-blue-600 hover:bg-blue-700" />
    <x-confirm-action-modal name="reject-work-request" action="{{ route('admin.bookings.reject', $booking) }}" title="Reject Work Request" message="Provide a reason for rejecting work request {{ $booking->reference_number }}." confirmText="Reject Request" icon="fa-times">
        <label for="booking-rejection-remarks" class="block text-sm font-semibold text-gray-700 mb-2">Reason for Rejection</label>
        <textarea id="booking-rejection-remarks" name="remarks" rows="4" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:border-red-400 focus:ring-2 focus:ring-red-100" placeholder="Please provide a reason"></textarea>
    </x-confirm-action-modal>
@endif
@if($booking->status == 'approved')
    <x-confirm-action-modal name="complete-work-request" action="{{ route('admin.bookings.complete', $booking) }}" title="Confirm Completion" message="Mark work request {{ $booking->reference_number }} as completed?" confirmText="Mark as Completed" icon="fa-check" confirmClass="bg-blue-600 hover:bg-blue-700" />
@endif
@if(in_array($booking->status, ['pending', 'approved']))
    <x-confirm-action-modal name="cancel-work-request" action="{{ route('admin.bookings.cancel', $booking) }}" title="Confirm Cancellation" message="Cancel work request {{ $booking->reference_number }}?" confirmText="Cancel Work Request" icon="fa-ban" />
@endif
@if(in_array($booking->status, ['completed', 'rejected'], true))
    <x-confirm-action-modal name="trash-work-request" action="{{ route('admin.bookings.destroy', $booking) }}" method="DELETE" title="Confirm Deletion" message="Move work request {{ $booking->reference_number }} to trash? It can be restored later." confirmText="Move to Trash" icon="fa-trash" />
@endif
@endsection
