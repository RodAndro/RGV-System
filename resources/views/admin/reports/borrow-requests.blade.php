@extends('layouts.admin')

@section('title', 'Borrow Requests Report - Admin Dashboard')

@section('header', 'Borrow Requests Report')

@section('content')
<div class="p-8">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('admin.reports.index') }}" class="text-gray-600 hover:text-[#74c365] transition-colors flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Back to Reports
        </a>
    </div>

    <!-- Filters Section -->
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-filter"></i>Filter Borrow Requests</h2>
    </div>
    <div class="card-mantis p-6 mb-8">
        <form method="GET" action="{{ route('admin.reports.borrow-requests') }}">
            <div class="flex flex-wrap gap-4">
                <select name="status" class="px-4 py-2.5 pr-10 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                    <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <button type="submit" class="btn-mantis px-6">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="{{ route('admin.reports.borrow-requests') }}" class="btn-mantis-outline px-6">Clear</a>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Requests</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-[#f0f9ef] w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-hand-holding text-[#74c365] text-xl"></i>
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
                    <p class="text-gray-500 text-sm">Borrowed</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['borrowed'] }}</p>
                </div>
                <div class="bg-blue-50 w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Returned</p>
                    <p class="text-3xl font-bold text-[#74c365]">{{ $stats['returned'] }}</p>
                </div>
                <div class="bg-[#f0f9ef] w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-undo text-[#74c365] text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    <!-- Borrow Requests Table Section -->
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-list"></i>All Borrow Requests Data</h2>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $borrowRequests->total() }} records</span>
            <x-per-page-selector />
        </div>
    </div>

    <div class="card-mantis overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Borrow Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($borrowRequests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $request->request_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->employee?->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->borrowItems->count() }} items</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->borrow_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->due_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge-mantis-{{ $request->status == 'pending' ? 'warning' : ($request->status == 'approved' ? 'success' : ($request->status == 'borrowed' ? 'warning' : ($request->status == 'returned' ? 'success' : 'danger'))) }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No borrow requests found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($borrowRequests->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $borrowRequests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<x-chatbot pageType="borrow_requests" />
@endpush
