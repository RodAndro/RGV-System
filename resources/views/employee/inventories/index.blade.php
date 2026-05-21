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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($inventories as $inventory)
            <div class="card-mantis overflow-hidden">
                @if($inventory->image_path)
                    <img src="{{ asset('storage/' . $inventory->image_path) }}" alt="{{ $inventory->name }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-box text-gray-300 text-5xl"></i>
                    </div>
                @endif
                <div class="p-4">
                    @if($inventory->isLowStock())
                        <div class="mb-2"><span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Low Stock Alert</span></div>
                    @endif
                    <div class="flex items-center space-x-2 mb-1">
                        <span class="text-xs font-mono text-[#2563eb] font-semibold">{{ $inventory->item_code }}</span>
                        <span class="badge-mantis-success">{{ ucfirst($inventory->condition) }}</span>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">{{ $inventory->name }}</h3>
                    <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $inventory->description ?? 'No description' }}</p>
                    <div class="flex items-center justify-between text-sm mb-3">
                        <span class="text-gray-500"><i class="fas fa-tag mr-1"></i>{{ $inventory->category->name }}</span>
                        <span class="font-semibold text-gray-800">{{ $inventory->quantity }} {{ $inventory->unit }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <a href="{{ route('employee.inventories.show', $inventory) }}" class="text-[#2563eb] hover:text-[#1d4ed8] font-semibold text-sm">
                            <i class="fas fa-eye mr-1"></i>View Details
                        </a>
                        <a href="{{ route('employee.borrow-requests.create') }}?item={{ $inventory->id }}" class="btn-mantis text-sm px-4 py-2">
                            <i class="fas fa-hand-holding mr-1"></i>Borrow
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-boxes text-gray-400 text-2xl"></i>
                </div>
                <p class="font-medium">No inventory items found</p>
                <a href="{{ route('employee.inventories.index') }}" class="text-[#2563eb] hover:text-[#1d4ed8] font-semibold mt-4 inline-block">Clear filters</a>
            </div>
        @endforelse
    </div>
    
    @if($inventories->hasPages())
        <div class="mt-8">{{ $inventories->links() }}</div>
    @endif
</div>
@endsection
