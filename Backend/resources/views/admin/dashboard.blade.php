@extends('layouts.admin')

@section('title', 'Admin Dashboard - RGV Multi-Tech Services')

@section('header', 'Dashboard')

@section('content')
<div class="space-y-8 p-5 md:p-8">
    <section>
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="font-sans text-base font-semibold text-gray-800 dark:text-gray-100">RGV Multi-Tech Services</p>
                <p class="mt-1 font-sans text-sm font-normal text-gray-500 dark:text-gray-400">A current view of requests, inventory, and team operations.</p>
            </div>
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 self-start rounded-lg bg-[#0f6f9d] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0a5c83] sm:self-auto">
                <i class="fas fa-list-check"></i>
                View work requests
            </a>
        </div>

        <div class="grid grid-cols-1 items-stretch gap-5 xl:grid-cols-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:col-span-3">
                <a href="{{ route('admin.bookings.index') }}" class="card-mantis group flex min-h-[108px] flex-col justify-between p-3 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total work requests</p>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-[#0f6f9d] dark:bg-sky-900/30">
                            <i class="fas fa-clipboard-list text-lg"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ number_format($stats['total_bookings']) }}</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Across all request statuses</p>
                    </div>
                </a>

                <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="card-mantis group flex min-h-[108px] flex-col justify-between p-3 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending work requests</p>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30">
                            <i class="fas fa-clock text-lg"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-xl font-bold tracking-tight text-amber-600">{{ number_format($stats['pending_bookings']) }}</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Awaiting review</p>
                    </div>
                </a>

                <a href="{{ route('admin.inventories.index') }}" class="card-mantis group flex min-h-[108px] flex-col justify-between p-3 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Inventory items</p>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-[#0f6f9d] dark:bg-sky-900/30">
                            <i class="fas fa-boxes-stacked text-lg"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ number_format($stats['inventory_count']) }}</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ number_format($stats['inventory_available']) }} available &middot; {{ number_format($stats['low_stock_alerts']) }} low stock</p>
                    </div>
                </a>

                <a href="{{ route('admin.users.index') }}" class="card-mantis group flex min-h-[108px] flex-col justify-between p-3 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Employees</p>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-[#0f6f9d] dark:bg-sky-900/30">
                            <i class="fas fa-users text-lg"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ number_format($stats['total_employees']) }}</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ number_format($stats['active_employees']) }} active accounts</p>
                    </div>
                </a>
            </div>

            <a href="{{ url('/#projects') }}" class="group relative flex min-h-[220px] overflow-hidden rounded-2xl bg-[#0b3550] shadow-md xl:col-span-2 xl:min-h-full" aria-label="Explore RGV project highlights">
                <img src="{{ asset('project-highlights/1.jpg') }}" alt="RGV project highlight at a Jollibee site" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-900/20 to-slate-900/5"></div>
                <div class="relative flex w-full flex-col justify-between p-4 text-white md:p-5">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full border border-white/30 bg-slate-950/25 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] backdrop-blur-sm">
                        <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                        RGV project highlight
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-100">Cagayan de Oro &middot; Multi-tech service team</p>
                        <h2 class="mt-2 max-w-lg text-xl font-bold leading-tight md:text-2xl">Technical work for real spaces and everyday operations.</h2>
                        <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-white/95">
                            Explore project highlights
                            <i class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                        </span>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <section>
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-chart-bar"></i>Analytics Overview</h2>
        </div>
        <div class="grid grid-cols-1 items-stretch gap-5 xl:grid-cols-12">
            <div class="card-mantis min-w-0 p-5 md:p-6 xl:col-span-7">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Work Request Status</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Work request status distribution</p>
                <div class="mt-2 min-w-0" style="height:180px"><canvas id="statusChart" aria-label="Work request status distribution"></canvas></div>
            </div>

            <aside class="card-mantis xl:col-span-5">
                <div class="border-b border-gray-100 px-4 py-2 dark:border-slate-700 md:px-5">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#0f6f9d]">Live operations</p>
                    <h2 class="mt-1 text-lg font-bold text-gray-900 dark:text-gray-100">Daily Operations Summary</h2>
                </div>
                <div class="space-y-1.5 p-3">
                    <a href="{{ route('admin.borrow-requests.index', ['status' => 'pending']) }}" class="flex items-center justify-between gap-2 rounded-xl bg-amber-50 px-3 py-1.5 transition hover:bg-amber-100 dark:bg-amber-900/20 dark:hover:bg-amber-900/30">
                        <span class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-amber-600 shadow-sm dark:bg-slate-800"><i class="fas fa-clock"></i></span>
                            <span><span class="block text-sm font-semibold text-gray-800 dark:text-gray-100">Borrow requests to review</span><span class="text-xs text-gray-500 dark:text-gray-400">Awaiting admin decision</span></span>
                        </span>
                        <strong class="text-base text-amber-700 dark:text-amber-300">{{ number_format($stats['pending_borrow_requests']) }}</strong>
                    </a>

                    <a href="{{ route('admin.inventories.low-stock') }}" class="flex items-center justify-between gap-2 rounded-xl bg-rose-50 px-3 py-1.5 transition hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/30">
                        <span class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-rose-600 shadow-sm dark:bg-slate-800"><i class="fas fa-triangle-exclamation"></i></span>
                            <span><span class="block text-sm font-semibold text-gray-800 dark:text-gray-100">Low-stock items</span><span class="text-xs text-gray-500 dark:text-gray-400">Inventory needing attention</span></span>
                        </span>
                        <strong class="text-base text-rose-700 dark:text-rose-300">{{ number_format($stats['low_stock_alerts']) }}</strong>
                    </a>

                    <a href="{{ route('admin.audit.index') }}" class="flex items-center justify-between gap-2 rounded-xl bg-sky-50 px-3 py-1.5 transition hover:bg-sky-100 dark:bg-sky-900/20 dark:hover:bg-sky-900/30">
                        <span class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-[#0f6f9d] shadow-sm dark:bg-slate-800"><i class="fas fa-list-check"></i></span>
                            <span><span class="block text-sm font-semibold text-gray-800 dark:text-gray-100">Audit events today</span><span class="text-xs text-gray-500 dark:text-gray-400">Recorded system activity</span></span>
                        </span>
                        <strong class="text-base text-[#0f6f9d] dark:text-sky-300">{{ number_format($stats['audit_events_today']) }}</strong>
                    </a>

                </div>
            </aside>
        </div>
    </section>

    <section>
        <div class="card-mantis min-w-0 overflow-hidden">
            <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-4 dark:border-slate-700 md:px-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Recent Work Requests</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">The latest service requests submitted to your team.</p>
                </div>
                <a href="{{ route('admin.bookings.index') }}" class="shrink-0 text-sm font-semibold text-[#0f6f9d] hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] border-collapse text-left">
                    <thead>
                        <tr class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-slate-800 dark:text-gray-400">
                            <th class="px-5 py-3 font-semibold md:px-6">Work request</th>
                            <th class="px-5 py-3 font-semibold">Customer</th>
                            <th class="px-5 py-3 font-semibold">Purpose</th>
                            <th class="px-5 py-3 font-semibold">Preferred date</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentBookings->take(5) as $booking)
                            <tr class="border-t border-gray-100 transition hover:bg-gray-50 dark:border-slate-700 dark:hover:bg-slate-800/60">
                                <td class="px-5 py-4 md:px-6">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono text-sm font-semibold text-[#0f6f9d] hover:underline">{{ $booking->reference_number }}</a>
                                </td>
                                <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $booking->full_name }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ ucfirst(str_replace('-', ' ', $booking->purpose_category)) }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $booking->preferred_date?->format('M d, Y') ?? 'Not scheduled' }}</td>
                                <td class="px-5 py-4 text-sm">
                                    @if ($booking->status === 'pending')
                                        <span class="badge-mantis-warning">Pending</span>
                                    @elseif ($booking->status === 'approved')
                                        <span class="badge-mantis-success">Approved</span>
                                    @elseif ($booking->status === 'rejected')
                                        <span class="badge-mantis-danger">Rejected</span>
                                    @elseif ($booking->status === 'completed')
                                        <span class="badge-mantis-success">Completed</span>
                                    @else
                                        <span class="badge-mantis-info">{{ ucfirst($booking->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No recent work requests yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </section>
</div>
<script>
(function initCharts() {
    if (typeof Chart === 'undefined') {
        setTimeout(initCharts, 100);
        return;
    }
    var statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        var workRequestStatuses = ['pending', 'approved', 'completed', 'rejected'];
        var workRequestsUrl = @json(route('admin.bookings.index'));
        var openWorkRequestStatus = function (index) {
            var status = workRequestStatuses[index];
            if (status) window.location.href = workRequestsUrl + '?status=' + encodeURIComponent(status);
        };

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
                plugins: {
                    legend: {
                        position: 'bottom',
                        onClick: function (event, legendItem) {
                            openWorkRequestStatus(legendItem.index);
                        },
                        onHover: function (event) {
                            if (event.native) event.native.target.style.cursor = 'pointer';
                        },
                        onLeave: function (event) {
                            if (event.native) event.native.target.style.cursor = 'default';
                        }
                    }
                },
                onClick: function (event, elements) {
                    if (elements.length) openWorkRequestStatus(elements[0].index);
                },
                onHover: function (event, elements) {
                    if (event.native) event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                }
            }
        });
    }
})();
</script>
@endsection
