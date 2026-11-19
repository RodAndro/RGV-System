@extends('layouts.employee')

@section('title', 'Employee Dashboard - RGV Multi-Tech Services')

@section('header', 'Dashboard')

@section('content')
<div class="p-4 md:p-8">
                <!-- Stats Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-chart-pie"></i>My Statistics</h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-4">
                    <div class="card-mantis p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">My Requests</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $stats['my_borrow_requests'] }}</p>
                            </div>
                            <div class="bg-[#eff6ff] w-10 h-10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-hand-holding text-[#2563eb] text-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div class="card-mantis p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Pending</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_requests'] }}</p>
                            </div>
                            <div class="bg-yellow-50 w-10 h-10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600 text-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div class="card-mantis p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Approved</p>
                                <p class="text-2xl font-bold text-[#2563eb]">{{ $stats['approved_requests'] }}</p>
                            </div>
                            <div class="bg-[#eff6ff] w-10 h-10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-[#2563eb] text-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div class="card-mantis p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Borrowed</p>
                                <p class="text-2xl font-bold text-[#1d4ed8]">{{ $stats['borrowed_items'] }}</p>
                            </div>
                            <div class="bg-[#eff6ff] w-10 h-10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-[#2563eb] text-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div class="card-mantis p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Returned</p>
                                <p class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ $stats['returned_items'] }}</p>
                            </div>
                            <div class="bg-gray-100 w-10 h-10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-undo text-gray-600 dark:text-gray-300 text-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-divider"></div>

                <!-- Available Inventory Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-boxes"></i>Available Inventory</h2>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Available Inventory -->
                    <div class="card-mantis p-6 h-[440px] flex flex-col">
                        <div class="flex justify-between items-center mb-4 shrink-0">
                            <h3 class="text-lg font-bold text-gray-800">Available Inventory</h3>
                            <a href="{{ route('employee.inventories.index') }}" class="text-[#2563eb] text-sm font-semibold hover:underline">View All</a>
                        </div>
                        <div class="min-h-0 flex-1 overflow-auto">
                            <table class="w-full border-collapse">
                                <thead class="sticky top-0 z-10 bg-gradient-to-r from-[#eff6ff] to-white dark:from-gray-800 dark:to-gray-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Item</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Category</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($availableInventory as $item)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                            <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                                <p class="font-semibold text-sm text-gray-800 dark:text-gray-100">{{ $item->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $item->item_code }}</p>
                                            </td>
                                            <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">{{ $item->category?->name ?? 'Uncategorized' }}</td>
                                            <td class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $item->quantity }} {{ $item->unit }}</span>
                                                @if($item->isLowStock())
                                                    <span class="text-xs text-red-600 ml-1" title="Low stock"><i class="fas fa-exclamation-triangle"></i></span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No available inventory</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- My Borrow Requests -->
                    <div class="card-mantis p-6 h-[440px] flex flex-col">
                        <div class="flex justify-between items-center mb-4 shrink-0">
                            <h3 class="text-lg font-bold text-gray-800">My Recent Requests</h3>
                            <a href="{{ route('employee.borrow-requests.index') }}" class="text-[#2563eb] text-sm font-semibold hover:underline">View All</a>
                        </div>
                        <div class="min-h-0 flex-1 overflow-auto">
                            <table class="w-full border-collapse">
                                <thead class="sticky top-0 z-10 bg-gradient-to-r from-[#eff6ff] to-white dark:from-gray-800 dark:to-gray-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Request #</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Borrow Period</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($myBorrowRequests as $request)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                            <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 font-mono text-xs font-semibold text-[#2563eb]">{{ $request->request_number }}</td>
                                            <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">{{ $request->borrow_date->format('M d, Y') }} - {{ $request->due_date->format('M d, Y') }}</td>
                                            <td class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
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
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No borrow requests yet</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-6 card-mantis p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('employee.borrow-requests.create') }}" class="btn-mantis flex items-center justify-center p-4">
                            <i class="fas fa-plus-circle mr-2"></i>New Borrow Request
                        </a>
                        <a href="{{ route('employee.borrow-requests.index') }}" class="flex items-center justify-center p-4 bg-gray-700 text-white rounded-xl hover:bg-gray-800 transition">
                            <i class="fas fa-list mr-2"></i>View My Requests
                        </a>
                        <a href="{{ route('employee.inventories.index') }}" class="flex items-center justify-center p-4 border-2 border-gray-200 rounded-xl hover:bg-gray-50 transition">
                            <i class="fas fa-boxes mr-2"></i>View Inventory
                        </a>
                    </div>
                </div>
</div>
@endsection
