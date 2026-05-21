@extends('layouts.admin')

@section('title', 'Reports - Admin Dashboard')

@section('header', 'Reports')

@section('content')
<div class="p-8">
                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Bookings</p>
                                <p class="text-3xl font-bold text-gray-800">{{ \App\Models\Booking::count() }}</p>
                            </div>
                            <div class="bg-blue-50 w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-calendar text-blue-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Inventory</p>
                                <p class="text-3xl font-bold text-gray-800">{{ \App\Models\Inventory::count() }}</p>
                            </div>
                            <div class="bg-[#eff6ff] w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-boxes text-[#2563eb] text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Borrow Requests</p>
                                <p class="text-3xl font-bold text-gray-800">{{ \App\Models\BorrowRequest::count() }}</p>
                            </div>
                            <div class="bg-yellow-50 w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-hand-holding text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Users</p>
                                <p class="text-3xl font-bold text-gray-800">{{ \App\Models\User::count() }}</p>
                            </div>
                            <div class="bg-purple-50 w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-users text-purple-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-divider"></div>

            <!-- Reports Section -->
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-chart-bar"></i>Available Reports</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="card-mantis p-6">
                    <a href="{{ route('admin.reports.bookings') }}" class="block hover:opacity-80 transition-opacity">
                        <div class="flex items-center">
                            <div class="bg-[#eff6ff] p-4 rounded-xl">
                                <i class="fas fa-calendar-alt text-[#2563eb] text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Bookings Report</h3>
                                <p class="text-gray-600 text-sm">View all booking data</p>
                            </div>
                        </div>
                    </a>
                    <div class="flex flex-wrap gap-1.5 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('admin.reports.bookings', ['status' => 'pending']) }}" class="px-2.5 py-1 bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 rounded-lg text-xs font-medium hover:bg-yellow-100 dark:hover:bg-yellow-900/40 transition-colors">Pending</a>
                        <a href="{{ route('admin.reports.bookings', ['status' => 'approved']) }}" class="px-2.5 py-1 bg-green-50 text-green-700 dark:bg-blue-900/20 dark:text-blue-400 rounded-lg text-xs font-medium hover:bg-green-100 dark:hover:bg-blue-900/40 transition-colors">Approved</a>
                        <a href="{{ route('admin.reports.bookings', ['status' => 'completed']) }}" class="px-2.5 py-1 bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 rounded-lg text-xs font-medium hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">Completed</a>
                        <a href="{{ route('admin.reports.bookings', ['status' => 'rejected']) }}" class="px-2.5 py-1 bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 rounded-lg text-xs font-medium hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">Rejected</a>
                    </div>
                </div>

                <div class="card-mantis p-6">
                    <a href="{{ route('admin.reports.inventory') }}" class="block hover:opacity-80 transition-opacity">
                        <div class="flex items-center">
                            <div class="bg-[#eff6ff] p-4 rounded-xl">
                                <i class="fas fa-boxes text-[#2563eb] text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Inventory Report</h3>
                                <p class="text-gray-600 text-sm">View inventory status</p>
                            </div>
                        </div>
                    </a>
                    <div class="flex flex-wrap gap-1.5 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('admin.reports.inventory', ['status' => 'available']) }}" class="px-2.5 py-1 bg-green-50 text-green-700 dark:bg-blue-900/20 dark:text-blue-400 rounded-lg text-xs font-medium hover:bg-green-100 dark:hover:bg-blue-900/40 transition-colors">Available</a>
                        <a href="{{ route('admin.reports.inventory', ['status' => 'borrowed']) }}" class="px-2.5 py-1 bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 rounded-lg text-xs font-medium hover:bg-yellow-100 dark:hover:bg-yellow-900/40 transition-colors">Borrowed</a>
                        <a href="{{ route('admin.reports.inventory', ['status' => 'maintenance']) }}" class="px-2.5 py-1 bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 rounded-lg text-xs font-medium hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">Maintenance</a>
                        <a href="{{ route('admin.reports.inventory', ['status' => 'damaged']) }}" class="px-2.5 py-1 bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 rounded-lg text-xs font-medium hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">Damaged</a>
                    </div>
                </div>

                <div class="card-mantis p-6">
                    <a href="{{ route('admin.reports.borrow-requests') }}" class="block hover:opacity-80 transition-opacity">
                        <div class="flex items-center">
                            <div class="bg-[#eff6ff] p-4 rounded-xl">
                                <i class="fas fa-hand-holding text-[#2563eb] text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Borrow Requests Report</h3>
                                <p class="text-gray-600 text-sm">View borrow request data</p>
                            </div>
                        </div>
                    </a>
                    <div class="flex flex-wrap gap-1.5 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('admin.reports.borrow-requests', ['status' => 'pending']) }}" class="px-2.5 py-1 bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 rounded-lg text-xs font-medium hover:bg-yellow-100 dark:hover:bg-yellow-900/40 transition-colors">Pending</a>
                        <a href="{{ route('admin.reports.borrow-requests', ['status' => 'approved']) }}" class="px-2.5 py-1 bg-green-50 text-green-700 dark:bg-blue-900/20 dark:text-blue-400 rounded-lg text-xs font-medium hover:bg-green-100 dark:hover:bg-blue-900/40 transition-colors">Approved</a>
                        <a href="{{ route('admin.reports.borrow-requests', ['status' => 'borrowed']) }}" class="px-2.5 py-1 bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 rounded-lg text-xs font-medium hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">Borrowed</a>
                        <a href="{{ route('admin.reports.borrow-requests', ['status' => 'returned']) }}" class="px-2.5 py-1 bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400 rounded-lg text-xs font-medium hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-colors">Returned</a>
                    </div>
                </div>

            </div>

            <div class="section-divider"></div>
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-download"></i>Export Reports</h2>
            </div>

            <div class="card-mantis p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border border-gray-200 rounded-xl p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-[#eff6ff] p-4 rounded-xl">
                                <i class="fas fa-calendar-alt text-[#2563eb] text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Export Bookings</h3>
                                <p class="text-gray-600 text-sm">Download bookings report</p>
                            </div>
                        </div>
                        <div class="flex space-x-2 flex-wrap gap-1">
                            <a href="{{ route('admin.import-export.bookings.export', ['format' => 'pdf']) }}" class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors font-medium text-xs">
                                <i class="fas fa-file-pdf mr-1"></i>PDF
                            </a>
                            <a href="{{ route('admin.import-export.bookings.export', ['format' => 'xlsx']) }}" class="px-3 py-1.5 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors font-medium text-xs">
                                <i class="fas fa-file-excel mr-1"></i>Excel
                            </a>
                        </div>
                    </div>

                    <div class="border border-gray-200 rounded-xl p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-[#eff6ff] p-4 rounded-xl">
                                <i class="fas fa-boxes text-[#2563eb] text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Export Inventory</h3>
                                <p class="text-gray-600 text-sm">Download inventory report</p>
                            </div>
                        </div>
                        <div class="flex space-x-2 flex-wrap gap-1">
                            <a href="{{ route('admin.import-export.inventory.export', ['format' => 'pdf']) }}" class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors font-medium text-xs">
                                <i class="fas fa-file-pdf mr-1"></i>PDF
                            </a>
                            <a href="{{ route('admin.import-export.inventory.export', ['format' => 'xlsx']) }}" class="px-3 py-1.5 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors font-medium text-xs">
                                <i class="fas fa-file-excel mr-1"></i>Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

@endsection
