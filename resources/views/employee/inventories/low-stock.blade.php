@extends('layouts.employee')

@section('title', 'Low Stock Alerts - RGV Multi-Tech Services')

@section('header', 'Low Stock Alerts')

@section('content')
<div class="p-8">
    <div class="card-mantis p-4 mb-6">
        <form action="{{ route('employee.inventories.low-stock') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search items..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-[#74c365]">
            <select name="category" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-[#74c365]">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn-mantis text-sm px-4 py-2">Filter</button>
                <a href="{{ route('employee.inventories.low-stock') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">Showing {{ $inventories->total() }} low stock items</p>
        <x-per-page-selector />
    </div>

    <div class="card-mantis overflow-hidden">
        <table class="w-full">
            <thead class="bg-red-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-red-700 uppercase tracking-wider">Current Stock</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-red-700 uppercase tracking-wider">Threshold</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($inventories as $item)
                    <tr class="hover:bg-red-50/30">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($item->image_path)
                                    <img src="{{ asset('storage/' . $item->image_path) }}" class="w-10 h-10 object-cover rounded-lg mr-3">
                                @else
                                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-box text-red-500"></i>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $item->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $item->item_code }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $item->category->name }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-red-600 font-bold">{{ $item->quantity }} {{ $item->unit }}</span>
                        </td>
                        <td class="px-6 py-4 text-center text-gray-500">{{ $item->low_stock_threshold }} {{ $item->unit }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-check-circle text-green-300 text-4xl mb-3 block"></i>
                            No low stock items — all inventory is well stocked!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($inventories->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $inventories->links() }}</div>
        @endif
    </div>
</div>
@endsection
