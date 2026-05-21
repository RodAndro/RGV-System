@extends('layouts.admin')

@section('title', 'Borrow Requests - Admin Dashboard')

@section('header', 'Borrow Requests')

@section('content')
<div class="p-8">
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 mb-4">
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-gray-50 dark:bg-gray-800 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-hand-holding text-gray-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">All</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $stats['total'] }}</p>
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
                        <div class="bg-blue-50 dark:bg-blue-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check-circle text-blue-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Approved</p>
                            <p class="text-lg font-bold text-blue-600">{{ $stats['approved'] }}</p>
                        </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-purple-50 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-truck-loading text-purple-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Borrowed</p>
                            <p class="text-lg font-bold text-purple-600">{{ $stats['borrowed'] }}</p>
                        </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-green-50 dark:bg-green-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-undo text-green-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Returned</p>
                            <p class="text-lg font-bold text-green-600">{{ $stats['returned'] }}</p>
                        </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-red-50 dark:bg-red-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-times-circle text-red-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Rejected</p>
                            <p class="text-lg font-bold text-red-600">{{ $stats['rejected'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-filter"></i>Filter</h2>
                </div>
                <div class="card-mantis p-4 mb-6">
                    <form action="{{ route('admin.borrow-requests.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <select name="status" class="pl-3 pr-8 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                            <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        <input type="text" name="search" placeholder="Search by request number or employee..." value="{{ request('search') }}" class="flex-1 px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400">
                        <button type="submit" class="btn-mantis px-4 py-2 text-sm">
                            <i class="fas fa-search mr-1"></i>Search
                        </button>
                        @if(request()->hasAny('status', 'search'))
                            <a href="{{ route('admin.borrow-requests.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm">
                                <i class="fas fa-times mr-1"></i>Clear
                            </a>
                        @endif
                    </form>
                </div>

                <div class="mb-4"></div>
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-hand-holding"></i>All Borrow Requests</h2>
                    <div class="flex items-center gap-2">
                        <x-per-page-selector />
                    </div>
                </div>
                <div class="card-mantis overflow-hidden">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-[#eff6ff] to-white dark:from-gray-800 dark:to-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Request #</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Employee</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Items</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Borrow Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Due Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($borrowRequests as $request)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 font-medium text-xs text-[#2563eb]">{{ $request->request_number }}</td>
                                    <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-800 dark:text-gray-100">{{ $request->employee?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">{{ $request->borrowItems->count() }} items</td>
                                    <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">{{ $request->borrow_date->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">{{ $request->due_date->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                        @if($request->status == 'pending')
                                            <span class="badge-mantis-warning">{{ ucfirst($request->status) }}</span>
                                        @elseif($request->status == 'approved')
                                            <span class="badge-mantis-success">{{ ucfirst($request->status) }}</span>
                                        @elseif($request->status == 'borrowed')
                                            <span class="badge-mantis-warning">{{ ucfirst($request->status) }}</span>
                                        @elseif($request->status == 'returned')
                                            <span class="badge-mantis-success">{{ ucfirst($request->status) }}</span>
                                        @else
                                            <span class="badge-mantis-danger">{{ ucfirst($request->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 text-sm">
                                        <a href="{{ route('admin.borrow-requests.show', $request) }}" class="w-8 h-8 bg-[#eff6ff] text-[#2563eb] rounded-lg inline-flex items-center justify-center hover:bg-[#dbeafe] transition-colors mr-2" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($request->status == 'pending')
                                            <button onclick="approveRequest({{ $request->id }})" class="w-9 h-9 bg-[#eff6ff] text-[#2563eb] rounded-lg inline-flex items-center justify-center hover:bg-[#dbeafe] transition-colors mr-2" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @elseif($request->status == 'approved')
                                            <form action="{{ route('admin.borrow-requests.mark-borrowed', $request) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="w-9 h-9 bg-blue-100 text-blue-600 rounded-lg inline-flex items-center justify-center hover:bg-blue-200 transition-colors mr-2" title="Mark as Borrowed">
                                                    <i class="fas fa-hand-holding"></i>
                                                </button>
                                            </form>
                                        @elseif($request->status == 'borrowed')
                                            <form action="{{ route('admin.borrow-returns.return', $request) }}" method="POST" class="inline" onsubmit="return confirm('Return all items?')">
                                                @csrf
                                                <button type="submit" class="w-9 h-9 bg-green-100 text-green-600 rounded-lg inline-flex items-center justify-center hover:bg-green-200 transition-colors mr-2" title="Return Items">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <div class="w-16 h-16 bg-[#eff6ff] rounded-full flex items-center justify-center mx-auto mb-4">
                                            <i class="fas fa-hand-holding text-[#2563eb] text-2xl"></i>
                                        </div>
                                        <p class="font-medium">No borrow requests found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $borrowRequests->links() }}
                    </div>
                </div>
</div>
@endsection

@push('scripts')
<script>
function approveRequest(id) {
    if(confirm('Are you sure you want to approve this borrow request?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/borrow-requests/${id}/approve`;
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
