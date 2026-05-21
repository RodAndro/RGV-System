@extends('layouts.admin')

@section('title', 'Admin Dashboard - RGV Multi-Tech Services')

@section('header', 'Dashboard')

@section('content')
<div class="p-8">
                <!-- Stats Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-chart-line"></i>Overview Statistics</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <a href="{{ route('admin.bookings.index') }}" class="card-mantis p-6 block hover:shadow-lg transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Work Request</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['total_bookings'] }}</p>
                            </div>
                            <div class="bg-[#eff6ff] w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-calendar-check text-[#2563eb] text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-[#2563eb] font-semibold"><i class="fas fa-arrow-up mr-1"></i>12%</span>
                            <span class="text-gray-500 ml-2">from last month</span>
                        </div>
                    </a>

                    <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="card-mantis p-6 block hover:shadow-lg transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Pending Work Request</p>
                                <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending_bookings'] }}</p>
                            </div>
                            <div class="bg-yellow-50 w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-[#2563eb] text-sm font-semibold">View All Pending</span>
                        </div>
                    </a>

                    <a href="{{ route('admin.inventories.low-stock') }}" class="card-mantis p-6 block hover:shadow-lg transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Inventory Items</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['inventory_count'] }}</p>
                            </div>
                            <div class="bg-[#eff6ff] w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-boxes text-[#2563eb] text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-red-500 font-semibold">{{ $stats['low_stock_alerts'] }} low stock</span>
                            <span class="text-[#2563eb] ml-2 font-semibold">View Low Stock</span>
                        </div>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="card-mantis p-6 block hover:shadow-lg transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Employees</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['total_employees'] }}</p>
                            </div>
                            <div class="bg-[#eff6ff] w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-users text-[#2563eb] text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-[#2563eb] text-sm font-semibold">View All Employees</span>
                        </div>
                    </a>
                </div>

                <div class="section-divider"></div>

                <!-- Charts Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-chart-bar"></i>Analytics Overview</h2>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="card-mantis p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Monthly Work Request</h3>
                        <div style="height:260px"><canvas id="bookingsChart"></canvas></div>
                    </div>

                    <div class="card-mantis p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Work Request Status</h3>
                        <div style="height:260px"><canvas id="statusChart"></canvas></div>
                    </div>
                </div>

                <div class="section-divider"></div>

                <!-- Recent Activity Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-clock"></i>Recent Activity</h2>
                </div>
                <div class="card-mantis overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-base font-bold text-gray-800">Recent Activity</h3>
                        <a href="{{ route('admin.bookings.index') }}" class="text-[#2563eb] text-sm font-semibold hover:underline">View All</a>
                    </div>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                                <th class="px-6 py-2 border-b border-r border-gray-200">Name</th>
                                <th class="px-6 py-2 border-b border-r border-gray-200">Reference</th>
                                <th class="px-6 py-2 border-b border-r border-gray-200">Date</th>
                                <th class="px-6 py-2 border-b border-gray-200">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBookings->take(4) as $booking)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-3 border-b border-r border-gray-100 text-sm font-semibold text-gray-800">{{ $booking->full_name }}</td>
                                    <td class="px-6 py-3 border-b border-r border-gray-100 text-xs text-[#2563eb] font-mono">{{ $booking->reference_number }}</td>
                                    <td class="px-6 py-3 border-b border-r border-gray-100 text-sm text-gray-600">{{ $booking->preferred_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-3 border-b border-gray-100 text-sm">
                                        @if($booking->status == 'pending')
                                            <span class="badge-mantis-warning">{{ ucfirst($booking->status) }}</span>
                                        @elseif($booking->status == 'approved')
                                            <span class="badge-mantis-success">{{ ucfirst($booking->status) }}</span>
                                        @elseif($booking->status == 'rejected')
                                            <span class="badge-mantis-danger">{{ ucfirst($booking->status) }}</span>
                                        @elseif($booking->status == 'completed')
                                            <span class="badge-mantis-success">{{ ucfirst($booking->status) }}</span>
                                        @else
                                            <span class="badge-mantis-info">{{ ucfirst($booking->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No recent work request</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
</div>
<script>
(function initCharts() {
    if (typeof Chart === 'undefined') {
        setTimeout(initCharts, 100);
        return;
    }
    var bookingsCtx = document.getElementById('bookingsChart');
    if (bookingsCtx) {
        new Chart(bookingsCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Work Request',
                    data: @json(array_values($monthlyBookings)),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(116, 195, 101, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    var statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved', 'Completed', 'Rejected'],
                datasets: [{
                    data: [
                        {{ $stats['pending_bookings'] }},
                        {{ $stats['approved_bookings'] }},
                        {{ $stats['completed_bookings'] }},
                        {{ $stats['rejected_bookings'] }}
                    ],
                    backgroundColor: ['#fbbf24', '#3b82f6', '#10b981', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
})();
</script>
@endsection
