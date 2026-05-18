@extends('layouts.admin')

@section('title', 'Inventory Report - Admin Dashboard')

@section('header', 'Inventory Report')

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
        <h2 class="section-title"><i class="fas fa-filter"></i>Filter Inventory</h2>
    </div>
    <div class="card-mantis p-6 mb-8">
        <form method="GET" action="{{ route('admin.reports.inventory') }}">
            <div class="flex flex-wrap gap-4">
                <select name="status" class="px-4 py-2.5 pr-10 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                    <option value="">All Status</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="damaged" {{ request('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                </select>
                <button type="submit" class="btn-mantis px-6">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="{{ route('admin.reports.inventory') }}" class="btn-mantis-outline px-6">Clear</a>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Items</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-[#f0f9ef] w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-boxes text-[#74c365] text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Available</p>
                    <p class="text-3xl font-bold text-[#74c365]">{{ $stats['available'] }}</p>
                </div>
                <div class="bg-blue-50 w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Low Stock</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $stats['low_stock'] }}</p>
                </div>
                <div class="bg-yellow-50 w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Maintenance</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $stats['maintenance'] }}</p>
                </div>
                <div class="bg-orange-50 w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-wrench text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    <!-- Inventory Table Section -->
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-list"></i>All Inventory Data</h2>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $inventory->total() }} records</span>
            <x-per-page-selector />
        </div>
    </div>

    <div class="card-mantis overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($inventory as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $item->item_code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item->category->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $item->quantity }}
                            @if($item->isLowStock())
                                <span class="badge-mantis-danger"> (Low Stock)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge-mantis-{{ $item->status == 'available' ? 'success' : ($item->status == 'borrowed' ? 'warning' : ($item->status == 'maintenance' ? 'warning' : 'danger')) }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($item->condition) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No inventory items found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($inventory->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $inventory->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<x-chatbot pageType="inventory" />
@endpush
