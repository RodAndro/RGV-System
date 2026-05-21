@extends('layouts.admin')

@section('title', 'Borrow Request #' . $borrowRequest->request_number)

@section('header', 'Borrow Request Details')

@section('content')
<div class="p-8">
    <div class="mb-6">
        <a href="{{ route('admin.borrow-requests.index') }}" class="text-gray-600 hover:text-[#2563eb] transition-colors flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Back to Borrow Requests
        </a>
    </div>

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
                    <form action="{{ route('admin.borrow-requests.approve', $borrowRequest) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-mantis">Approve</button>
                    </form>
                    <button onclick="document.getElementById('reject-section').classList.toggle('hidden')" class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium">
                        <i class="fas fa-times mr-1"></i>Reject
                    </button>
                </div>
                <div id="reject-section" class="hidden mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl">
                    <form action="{{ route('admin.borrow-requests.reject', $borrowRequest) }}" method="POST">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Remarks</label>
                        <textarea name="remarks" rows="2" required class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] dark:bg-gray-800 dark:text-gray-100 mb-2"></textarea>
                        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium text-sm">Confirm Reject</button>
                    </form>
                </div>
            @elseif($borrowRequest->status == 'approved')
                <div class="space-x-2">
                    <form action="{{ route('admin.borrow-requests.mark-borrowed', $borrowRequest) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-mantis">Mark as Borrowed</button>
                    </form>
                </div>
            @elseif($borrowRequest->status == 'borrowed')
                <div class="space-x-2">
                    <form action="{{ route('admin.borrow-returns.return', $borrowRequest) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-mantis" onclick="return confirm('Mark all items in this request as returned?')">Return Items</button>
                    </form>
                </div>
            @endif
        </div>

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
@endsection
