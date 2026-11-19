@extends('layouts.employee')

@section('title', 'Available Inventory - RGV Multi-Tech Services')

@section('header', 'Available Inventory')

@section('content')
<div class="p-4 md:p-8">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-gray-50 dark:bg-gray-800 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-boxes text-gray-600"></i></div>
            <div class="min-w-0"><p class="text-xs text-gray-500 truncate">Total Items</p><p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $stats['total'] }}</p></div>
        </div>
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-blue-50 dark:bg-blue-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-tags text-blue-600"></i></div>
            <div class="min-w-0"><p class="text-xs text-gray-500 truncate">Categories</p><p class="text-lg font-bold text-blue-600">{{ $categories->count() }}</p></div>
        </div>
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-[#eff6ff] dark:bg-blue-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-check-circle text-[#2563eb]"></i></div>
            <div class="min-w-0"><p class="text-xs text-gray-500 truncate">Available</p><p class="text-lg font-bold text-[#2563eb]">{{ $stats['available'] }}</p></div>
        </div>
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-red-50 dark:bg-red-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-triangle text-red-600"></i></div>
            <div class="min-w-0"><p class="text-xs text-gray-500 truncate">Low Stock</p><p class="text-lg font-bold text-red-600">{{ $stats['low_stock'] }}</p></div>
        </div>
    </div>

    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-filter"></i>Filter Inventory</h2>
    </div>
    <div class="card-mantis p-4 mb-6">
        <form action="{{ route('employee.inventories.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search items..." aria-label="Search items"
                class="flex-1 min-w-[180px] px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400">
            <select name="category" aria-label="Filter by category" class="pl-3 pr-8 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-mantis px-4 py-2 text-sm">
                    <i class="fas fa-search mr-1"></i>Search
                </button>
                <a href="{{ route('employee.inventories.index') }}" class="btn-mantis-outline px-4 py-2 text-sm">
                    Clear
                </a>
        </form>
    </div>

    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-boxes"></i>Available Inventory</h2>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500">Showing {{ $inventories->count() }} of {{ $inventories->total() }} items</span>
            <x-per-page-selector />
        </div>
    </div>

    <div class="card-mantis overflow-hidden">
        <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                <thead class="bg-gradient-to-r from-[#eff6ff] to-white dark:from-gray-800 dark:to-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Item Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Quantity</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Condition</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventories as $inventory)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                <span class="font-mono text-xs font-semibold text-[#2563eb]">{{ $inventory->item_code }}</span>
                            </td>
                            <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                <p class="font-semibold text-sm text-gray-800 dark:text-gray-100">{{ $inventory->name }}</p>
                                @if($inventory->isLowStock())
                                    <span class="low-stock-alert"><i class="fas fa-exclamation-triangle"></i>Low Stock Alert</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">{{ $inventory->category?->name ?? 'Uncategorized' }}</td>
                            <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                <p class="font-semibold text-sm text-gray-800 dark:text-gray-100">{{ $inventory->quantity }} {{ $inventory->unit }}</p>
                            </td>
                            <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                <span class="badge-mantis-success">Available</span>
                            </td>
                            <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                <span class="badge-mantis-{{ in_array($inventory->condition, ['new', 'good'], true) ? 'success' : ($inventory->condition === 'fair' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($inventory->condition) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('employee.inventories.show', $inventory) }}" class="w-9 h-9 bg-[#eff6ff] text-[#2563eb] rounded-lg flex items-center justify-center hover:bg-[#dbeafe] transition-colors" title="View details" aria-label="View {{ $inventory->name }} details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('employee.borrow-requests.create') }}?item={{ $inventory->id }}" class="w-9 h-9 bg-[#0f6f9d] text-white rounded-lg flex items-center justify-center hover:bg-[#0a5c83] transition-colors" title="Borrow item" aria-label="Borrow {{ $inventory->name }}">
                                        <i class="fas fa-hand-holding"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="w-16 h-16 bg-[#eff6ff] rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-box-open text-[#2563eb] text-2xl"></i>
                                </div>
                                <p class="font-medium">No inventory items found</p>
                                <a href="{{ route('employee.inventories.index') }}" class="text-[#2563eb] hover:text-[#1d4ed8] font-semibold mt-4 inline-block">Clear filters</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inventories->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $inventories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
