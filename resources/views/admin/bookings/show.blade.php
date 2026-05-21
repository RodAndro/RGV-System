@extends('layouts.admin')

@section('title', 'Booking Details - Admin Dashboard')

@section('header', 'Booking Details')

@section('content')
<div class="p-4 md:p-8">
    @if(session('success'))
        <div class="bg-gradient-to-r from-[#eff6ff] to-[#dbeafe] dark:from-blue-900/20 dark:to-blue-800/10 border border-[#2563eb] dark:border-blue-700 text-[#1e40af] dark:text-blue-300 px-4 py-3 rounded-xl mb-6">
            {{ session('success') }}
        </div>
    @endif

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
                    <div><p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Preferred Time</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $booking->preferred_time }}</p></div>
                    <div class="md:col-span-2"><p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Address</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $booking->address }}</p></div>
                    <div class="md:col-span-2"><p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Reason</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $booking->reason }}</p></div>
                </div>
                @if($booking->attachment_path)
                    <div class="mt-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Attachment</p>
                        <a href="{{ asset('storage/' . $booking->attachment_path) }}" target="_blank" class="inline-flex items-center text-[#2563eb] hover:text-[#1d4ed8] font-medium">
                            <i class="fas fa-file-alt mr-2"></i>View Attachment
                        </a>
                    </div>
                @endif
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
                    <form action="{{ route('admin.bookings.approve', $booking) }}" method="POST" class="mb-4" onsubmit="return confirm('Approve this work request?');">
                        @csrf
                        <button type="submit" class="w-full btn-mantis"><i class="fas fa-check mr-2"></i>Approve Work Request</button>
                    </form>
                    <button onclick="showRejectModal()" class="w-full px-6 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium mb-4">
                        <i class="fas fa-times mr-2"></i>Reject Work Request
                    </button>
                @endif
                @if($booking->status == 'approved')
                    <form action="{{ route('admin.bookings.complete', $booking) }}" method="POST" class="mb-4" onsubmit="return confirm('Mark this work request as completed?');">
                        @csrf
                        <button type="submit" class="w-full btn-mantis"><i class="fas fa-check-circle mr-2"></i>Mark as Completed</button>
                    </form>
                @endif
                @if(in_array($booking->status, ['pending', 'approved']))
                    <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" class="mb-4" onsubmit="return confirm('Cancel this work request?');">
                        @csrf
                        <button type="submit" class="w-full px-6 py-3 bg-gray-700 dark:bg-gray-700 text-white rounded-xl hover:bg-gray-800 dark:hover:bg-gray-600 transition-all font-medium">
                            <i class="fas fa-ban mr-2"></i>Cancel Booking
                        </button>
                    </form>
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
                    <button type="submit" class="w-full btn-mantis"><i class="fas fa-user-plus mr-2"></i>Assign Employee</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="rejectModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 backdrop-blur-sm" onclick="if(event.target === this) hideRejectModal()">
    <div class="card-mantis p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Reject Work Request</h3>
            <button type="button" onclick="hideRejectModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form action="{{ route('admin.bookings.reject', $booking) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for Rejection *</label>
                <textarea name="remarks" rows="4" required class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-red-400 transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100" placeholder="Please provide a reason for rejecting this booking"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideRejectModal()" class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-all font-medium text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium text-sm shadow-lg shadow-red-500/25"><i class="fas fa-times mr-2"></i>Reject Booking</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showRejectModal() { document.getElementById('rejectModal').classList.remove('hidden'); document.getElementById('rejectModal').classList.add('flex'); }
    function hideRejectModal() { document.getElementById('rejectModal').classList.add('hidden'); document.getElementById('rejectModal').classList.remove('flex'); }
</script>
@endpush
