@extends('layouts.admin')

@section('title', 'Borrow Request #' . $borrowRequest->request_number)

@section('header', 'Borrow Request Details')

@section('content')
<div class="p-8">
    <a href="{{ route('admin.borrow-requests.index') }}" class="inline-flex items-center text-gray-600 hover:text-[#2563eb] transition-colors text-sm mb-3">
        <i class="fas fa-arrow-left mr-1"></i>Back to Borrow Requests
    </a>

    <div class="card-mantis p-6 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Borrow Request #{{ $borrowRequest->request_number }}</h1>
                @if($borrowRequest->status == 'pending')
                    <span class="badge-mantis-warning mt-2 inline-block">{{ ucfirst($borrowRequest->status) }}</span>
                @elseif($borrowRequest->status == 'approved')
                    <span class="badge-mantis-success mt-2 inline-block">{{ ucfirst($borrowRequest->status) }}</span>
                @elseif($borrowRequest->status == 'borrowed')
                    <span class="badge-mantis-warning mt-2 inline-block">{{ ucfirst($borrowRequest->status) }}</span>
                @elseif($borrowRequest->status == 'returned')
                    <span class="badge-mantis-success mt-2 inline-block">{{ ucfirst($borrowRequest->status) }}</span>
                @else
                    <span class="badge-mantis-danger mt-2 inline-block">{{ ucfirst($borrowRequest->status) }}</span>
                @endif
            </div>
            @if($borrowRequest->status == 'pending')
                <div class="space-x-2">
                    <button type="button" @click="$dispatch('open-modal', 'approve-borrow-request')" class="btn-mantis">Approve</button>
                    <button type="button" @click="$dispatch('open-modal', 'reject-borrow-request')" class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium">
                        <i class="fas fa-times mr-1"></i>Reject
                    </button>
                </div>
            @elseif($borrowRequest->status == 'approved')
                <div class="space-x-2">
                    <form action="{{ route('admin.borrow-requests.mark-borrowed', $borrowRequest) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-mantis">Mark as Borrowed</button>
                    </form>
                </div>
            @elseif($borrowRequest->status == 'borrowed')
                <button type="button" @click="$dispatch('open-modal', 'return-borrow-request')" class="btn-mantis">
                    <i class="fas fa-undo mr-2"></i>Return Items
                </button>
            @endif
        </div>

        @if($borrowRequest->returned_at)
            <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                <p class="text-green-800 dark:text-green-300 text-sm">
                    <i class="fas fa-map-marker-alt mr-2"></i>Returned at: {{ $borrowRequest->return_location ?: 'Not specified' }}
                </p>
                <p class="text-green-800 dark:text-green-300 text-sm mt-1">
                    <i class="fas fa-clock mr-2"></i>Returned on: {{ $borrowRequest->returned_at->format('F d, Y - g:i A') }}
                </p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Employee</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ $borrowRequest->employee?->name ?? 'N/A' }}</p>
                <p class="text-gray-600 dark:text-gray-400">{{ $borrowRequest->employee->email }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Dates</h3>
                <p class="text-gray-600 dark:text-gray-400">Borrow Date: {{ $borrowRequest->borrow_date->format('F d, Y') }}</p>
                <p class="text-gray-600 dark:text-gray-400">Due Date: {{ $borrowRequest->due_date->format('F d, Y') }}</p>
                @if($borrowRequest->return_date)
                    <p class="text-gray-600 dark:text-gray-400">Return Date: {{ $borrowRequest->return_date->format('F d, Y') }}</p>
                @endif
            </div>
        </div>

        @if($borrowRequest->reason)
            <div class="mt-6">
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Reason</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ $borrowRequest->reason }}</p>
            </div>
        @endif
    </div>

    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-boxes"></i>Borrowed Items</h2>
    </div>
    <div class="card-mantis overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Borrowed Condition</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Return Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Returned Condition</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($borrowRequest->borrowItems as $item)
                    <tr>
                        <td class="px-6 py-3 text-gray-800 dark:text-gray-200">{{ $item->inventory->name }}</td>
                        <td class="px-6 py-3 text-gray-800 dark:text-gray-200">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-gray-800 dark:text-gray-200">{{ ucfirst($item->condition_borrowed ?? 'good') }}</td>
                        <td class="px-6 py-3">
                            @if($item->is_returned)
                                <span class="badge-mantis-success">Returned</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">{{ $item->returned_at?->format('M d, Y') }}</span>
                            @else
                                <span class="badge-mantis-warning">Not Returned</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-800 dark:text-gray-200">{{ $item->condition_returned ? ucfirst($item->condition_returned) : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($borrowRequest->status == 'pending')
    <x-confirm-action-modal name="approve-borrow-request" action="{{ route('admin.borrow-requests.approve', $borrowRequest) }}" title="Confirm Approval" message="Approve borrow request #{{ $borrowRequest->request_number }}?" confirmText="Approve Request" icon="fa-check" confirmClass="bg-blue-600 hover:bg-blue-700" />
    <x-confirm-action-modal name="reject-borrow-request" action="{{ route('admin.borrow-requests.reject', $borrowRequest) }}" title="Reject Borrow Request" message="Provide a reason for rejecting borrow request #{{ $borrowRequest->request_number }}." confirmText="Reject Request" icon="fa-times">
        <label for="borrow-rejection-remarks" class="block text-sm font-semibold text-gray-700 mb-2">Remarks</label>
        <textarea id="borrow-rejection-remarks" name="remarks" rows="3" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:border-red-400 focus:ring-2 focus:ring-red-100"></textarea>
    </x-confirm-action-modal>
@elseif($borrowRequest->status == 'borrowed')
    <x-confirm-action-modal name="return-borrow-request" action="{{ route('admin.borrow-returns.return', $borrowRequest) }}" title="Confirm Return" message="Mark all items in borrow request #{{ $borrowRequest->request_number }} as returned?" confirmText="Return Items" icon="fa-undo" confirmClass="bg-blue-600 hover:bg-blue-700">
        <label for="borrow-return-location" class="block text-sm font-semibold text-gray-700 mb-2">Return Location</label>
        <input id="borrow-return-location" type="text" name="return_location" value="{{ old('return_location') }}" required maxlength="255" class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:border-blue-400 focus:ring-2 focus:ring-blue-100" placeholder="Describe where the items were returned">
    </x-confirm-action-modal>
@endif
@endsection
