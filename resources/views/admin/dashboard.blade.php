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
                            <div class="bg-[#f0f9ef] w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-calendar-check text-[#74c365] text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-[#74c365] font-semibold"><i class="fas fa-arrow-up mr-1"></i>12%</span>
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
                            <span class="text-[#74c365] text-sm font-semibold">View All Pending</span>
                        </div>
                    </a>

                    <a href="{{ route('admin.inventories.low-stock') }}" class="card-mantis p-6 block hover:shadow-lg transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Inventory Items</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['inventory_count'] }}</p>
                            </div>
                            <div class="bg-[#f0f9ef] w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-boxes text-[#74c365] text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-red-500 font-semibold">{{ $stats['low_stock_alerts'] }} low stock</span>
                            <span class="text-[#74c365] ml-2 font-semibold">View Low Stock</span>
                        </div>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="card-mantis p-6 block hover:shadow-lg transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Employees</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['total_employees'] }}</p>
                            </div>
                            <div class="bg-[#f0f9ef] w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-users text-[#74c365] text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-[#74c365] text-sm font-semibold">View All Employees</span>
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
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Work Requests -->
                    <div class="card-mantis p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-800">Recent Work Request</h3>
                            <a href="{{ route('admin.bookings.index') }}" class="text-[#74c365] text-sm font-semibold hover:underline">View All</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left text-gray-500 text-sm">
                                        <th class="pb-3">Name</th>
                                        <th class="pb-3">Date</th>
                                        <th class="pb-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentBookings as $booking)
                                        <tr class="border-t border-gray-100">
                                            <td class="py-3">
                                                <p class="font-semibold text-gray-800">{{ $booking->full_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $booking->reference_number }}</p>
                                            </td>
                                            <td class="py-3 text-sm text-gray-600 dark:text-gray-300">{{ $booking->preferred_date->format('M d, Y') }}</td>
                                            <td class="py-3">
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
                                            <td colspan="3" class="py-4 text-center text-gray-500">No recent work request</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pending Actions -->
                    <div class="card-mantis p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-800">Pending Actions</h3>
                        </div>
                        <div class="space-y-4">
                            @forelse($pendingBookings as $booking)
                                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-xl">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $booking->full_name }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $booking->preferred_date->format('M d, Y') }} at {{ $booking->preferred_time }}</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.bookings.show', $booking) }}" class="text-[#74c365] hover:text-[#5dad4f]">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.bookings.approve', $booking) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-[#74c365] hover:text-[#5dad4f]">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-500 py-4">No pending work request</p>
                            @endforelse
                        </div>
                    </div>
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
                    borderColor: '#74c365',
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
                    backgroundColor: ['#fbbf24', '#74c365', '#5dad4f', '#ef4444']
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
