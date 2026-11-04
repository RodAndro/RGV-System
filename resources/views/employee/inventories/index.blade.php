@extends('layouts.employee')

@section('title', 'Available Inventory - RGV Multi-Tech Services')

@section('header', 'Available Inventory')

@section('content')
<div class="p-8">
    <div class="card-mantis p-6 mb-8">
        <form action="{{ route('employee.inventories.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search items..." 
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 flex items-end space-x-2">
                <button type="submit" class="btn-mantis px-6 py-3">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('employee.inventories.index') }}" class="px-6 py-3 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div><p class="text-gray-500 text-sm">Total Items</p><p class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</p></div>
                <div class="bg-[#eff6ff] w-12 h-12 rounded-xl flex items-center justify-center"><i class="fas fa-boxes text-[#2563eb] text-xl"></i></div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div><p class="text-gray-500 text-sm">Categories</p><p class="text-3xl font-bold text-blue-600">{{ $categories->count() }}</p></div>
                <div class="bg-blue-50 w-12 h-12 rounded-xl flex items-center justify-center"><i class="fas fa-tags text-blue-600 text-xl"></i></div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div><p class="text-gray-500 text-sm">Available</p><p class="text-3xl font-bold text-[#2563eb]">{{ $stats['available'] }}</p></div>
                <div class="bg-[#eff6ff] w-12 h-12 rounded-xl flex items-center justify-center"><i class="fas fa-check-circle text-[#2563eb] text-xl"></i></div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div><p class="text-gray-500 text-sm">Low Stock Alerts</p><p class="text-3xl font-bold text-red-600">{{ $stats['low_stock'] }}</p></div>
                <div class="bg-red-50 w-12 h-12 rounded-xl flex items-center justify-center"><i class="fas fa-exclamation-triangle text-red-600 text-xl"></i></div>
            </div>
        </div>
    </div>

    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-boxes"></i>Available Inventory</h2>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500">Showing {{ $inventories->total() }} items</span>
            <x-per-page-selector />
        </div>
    </div>

    <div class="card-mantis overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gradient-to-r from-[#eff6ff] to-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Item Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Quantity</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Condition</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-gray-200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventories as $inventory)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 border-b border-r border-gray-100">
                                <span class="font-mono text-xs font-semibold text-[#2563eb]">{{ $inventory->item_code }}</span>
                            </td>
                            <td class="px-4 py-3 border-b border-r border-gray-100">
                                <p class="font-semibold text-sm text-gray-800">{{ $inventory->name }}</p>
                                @if($inventory->isLowStock())
                                    <span class="text-xs text-red-600">Low Stock Alert</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 border-b border-r border-gray-100 text-xs text-gray-600">{{ $inventory->category?->name ?? 'Uncategorized' }}</td>
                            <td class="px-4 py-3 border-b border-r border-gray-100">
                                <p class="font-semibold text-sm text-gray-800">{{ $inventory->quantity }} {{ $inventory->unit }}</p>
                            </td>
                            <td class="px-4 py-3 border-b border-r border-gray-100">
                                <span class="badge-mantis-success">Available</span>
                            </td>
                            <td class="px-4 py-3 border-b border-r border-gray-100">
                                <span class="badge-mantis-{{ in_array($inventory->condition, ['new', 'good'], true) ? 'success' : ($inventory->condition === 'fair' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($inventory->condition) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 border-b border-gray-100">
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
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
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
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $inventories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
