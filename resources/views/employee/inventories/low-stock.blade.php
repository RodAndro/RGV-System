@extends('layouts.employee')

@section('title', 'Low Stock Alerts - RGV Multi-Tech Services')

@section('header', 'Low Stock Alerts')

@section('content')
<div class="p-4 md:p-8">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-filter"></i>Filter Inventory</h2>
    </div>
    <div class="card-mantis p-4 mb-6">
        <form action="{{ route('employee.inventories.low-stock') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search items..." aria-label="Search items"
                class="flex-1 min-w-[180px] px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400">
            <select name="category" aria-label="Filter by category" class="pl-3 pr-8 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn-mantis text-sm px-4 py-2"><i class="fas fa-search mr-1"></i>Search</button>
                <a href="{{ route('employee.inventories.low-stock') }}" class="btn-mantis-outline px-4 py-2 text-sm">Clear</a>
            </div>
        </form>
    </div>

    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-exclamation-triangle"></i>Low Stock Items</h2>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $inventories->count() }} of {{ $inventories->total() }} low stock items</span>
            <x-per-page-selector />
        </div>
    </div>

    <div class="card-mantis overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead class="bg-gradient-to-r from-[#eff6ff] to-white dark:from-gray-800 dark:to-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Item</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Current Stock</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">Threshold</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventories as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="w-9 h-9 bg-red-50 dark:bg-red-900/30 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-box text-red-500"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-100">{{ $item->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $item->item_code }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">{{ $item->category?->name ?? 'Uncategorized' }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                            <span class="text-red-600 font-bold">{{ $item->quantity }} {{ $item->unit }}</span>
                        </td>
                        <td class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 text-sm text-yellow-600 dark:text-yellow-400">{{ $item->low_stock_threshold }} {{ $item->unit }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-check-circle text-green-300 text-4xl mb-3 block"></i>
                            No low stock items — all inventory is well stocked!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($inventories->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">{{ $inventories->links() }}</div>
        @endif
    </div>
</div>
@endsection
