@extends('layouts.employee')

@section('title', 'Employee Dashboard - RGV Multi-Tech Services')

@section('header', 'Dashboard')

@section('content')
<div class="p-8">
                <!-- Stats Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-chart-pie"></i>My Statistics</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">My Requests</p>
                                <p class="text-3xl font-bold text-gray-800">{{ $stats['my_borrow_requests'] }}</p>
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
                                <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending_requests'] }}</p>
                            </div>
                            <div class="bg-yellow-50 w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Approved</p>
                                <p class="text-3xl font-bold text-[#74c365]">{{ $stats['approved_requests'] }}</p>
                            </div>
                            <div class="bg-[#f0f9ef] w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-check-circle text-[#74c365] text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Borrowed</p>
                                <p class="text-3xl font-bold text-[#5dad4f]">{{ $stats['borrowed_items'] }}</p>
                            </div>
                            <div class="bg-[#f0f9ef] w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-box text-[#74c365] text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Returned</p>
                                <p class="text-3xl font-bold text-gray-600 dark:text-gray-300">{{ $stats['returned_items'] }}</p>
                            </div>
                            <div class="bg-gray-100 w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-undo text-gray-600 dark:text-gray-300 text-xl"></i>
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
                    <div class="card-mantis p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-800">Available Inventory</h3>
                            <a href="{{ route('employee.inventories.index') }}" class="text-[#74c365] text-sm font-semibold hover:underline">View All</a>
                        </div>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @forelse($availableInventory as $item)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                    <div class="flex items-center">
                                        @if($item->image_path)
                                            <img src="{{ asset('storage/' . $item->image_path) }}" class="w-12 h-12 object-cover rounded-xl mr-3">
                                        @else
                                            <div class="w-12 h-12 bg-[#f0f9ef] rounded-xl flex items-center justify-center mr-3">
                                                <i class="fas fa-box text-[#74c365]"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-semibold text-gray-800">{{ $item->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $item->category->name }} • {{ $item->quantity }} {{ $item->unit }}</p>
                                        </div>
                                    </div>
                                    @if($item->isLowStock())
                                        <span class="text-xs text-red-600"><i class="fas fa-exclamation-triangle"></i></span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-center text-gray-500 py-4">No available inventory</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- My Borrow Requests -->
                    <div class="card-mantis p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-800">My Recent Requests</h3>
                            <a href="{{ route('employee.borrow-requests.index') }}" class="text-[#74c365] text-sm font-semibold hover:underline">View All</a>
                        </div>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @forelse($myBorrowRequests as $request)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $request->request_number }}</p>
                                        <p class="text-sm text-gray-500">{{ $request->borrow_date->format('M d, Y') }} - {{ $request->due_date->format('M d, Y') }}</p>
                                    </div>
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
                                </div>
                            @empty
                                <p class="text-center text-gray-500 py-4">No borrow requests yet</p>
                            @endforelse
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
