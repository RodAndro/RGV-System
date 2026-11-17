@extends('layouts.employee')

@section('title', 'My Borrow Requests - RGV Multi-Tech Services')

@section('header', 'My Borrow Requests')

@section('content')
<div class="p-4 md:p-8">
                @if(session('success'))
                    <div class="bg-gradient-to-r from-[#eff6ff] to-[#dbeafe] border border-[#2563eb] text-[#1e40af] px-4 py-3 rounded-xl mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Summary Stats -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Total Requests</p>
                                <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $borrowRequests->total() }}</p>
                            </div>
                            <div class="bg-[#eff6ff] dark:bg-blue-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-hand-holding text-[#2563eb]"></i>
                            </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Pending</p>
                                <p class="text-lg font-bold text-yellow-600">{{ $borrowRequests->where('status', 'pending')->count() }}</p>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Borrowed</p>
                                <p class="text-lg font-bold text-blue-600">{{ $borrowRequests->where('status', 'borrowed')->count() }}</p>
                            </div>
                            <div class="bg-blue-50 dark:bg-blue-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-exchange-alt text-blue-600"></i>
                            </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Returned</p>
                                <p class="text-lg font-bold text-[#2563eb]">{{ $borrowRequests->where('status', 'returned')->count() }}</p>
                            </div>
                            <div class="bg-[#eff6ff] dark:bg-blue-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-undo text-[#2563eb]"></i>
                            </div>
                    </div>
                </div>

                <div class="section-divider"></div>

                <!-- Borrow Requests Table Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-list"></i>My Requests</h2>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $borrowRequests->total() }} requests</span>
                        <a href="{{ route('employee.borrow-requests.create') }}" class="btn-mantis px-3 py-2 text-sm">
                            <i class="fas fa-plus mr-1"></i>New Request
                        </a>
                    </div>
                </div>

                <div class="card-mantis overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-gradient-to-r from-[#eff6ff] to-white dark:from-gray-800 dark:to-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Request #</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Borrow Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Due Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Items</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($borrowRequests as $request)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                            <span class="font-mono text-sm font-semibold text-[#2563eb]">{{ $request->request_number }}</span>
                                        </td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">{{ $request->borrow_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">{{ $request->due_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">{{ $request->borrowItems->count() }} items</td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                            <span class="badge-mantis-{{ $request->status == 'pending' ? 'warning' : ($request->status == 'approved' ? 'success' : ($request->status == 'borrowed' ? 'warning' : ($request->status == 'returned' ? 'success' : 'danger'))) }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('employee.borrow-requests.show', $request) }}" class="w-9 h-9 bg-[#eff6ff] text-[#2563eb] rounded-lg flex items-center justify-center hover:bg-[#dbeafe] transition-colors" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($request->status == 'borrowed')
                                                    <a href="{{ route('employee.borrow-requests.show', $request) }}#return" class="w-9 h-9 bg-[#eff6ff] text-[#2563eb] rounded-lg flex items-center justify-center hover:bg-[#dbeafe] transition-colors" title="Return Items">
                                                        <i class="fas fa-undo"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                            <div class="w-16 h-16 bg-[#eff6ff] rounded-full flex items-center justify-center mx-auto mb-4">
                                                <i class="fas fa-hand-holding text-[#2563eb] text-2xl"></i>
                                            </div>
                                            <p class="font-medium">No borrow requests yet</p>
                                            <a href="{{ route('employee.borrow-requests.create') }}" class="btn-mantis mt-4 inline-block">Create your first request</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($borrowRequests->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                            {{ $borrowRequests->links() }}
                        </div>
                    @endif
                </div>
@endsection
