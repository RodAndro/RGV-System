@extends('layouts.admin')

@section('title', 'Low Stock Alerts - Admin Dashboard')

@section('header', 'Low Stock Alerts')

@section('content')
<div class="p-4 md:p-8">
    <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-white"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">Low Stock Alert</h3>
                <p class="text-sm text-red-600 dark:text-red-400 font-medium">{{ $inventories->count() }} item(s) below threshold</p>
            </div>
        </div>
    </div>

    @if($inventories->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($inventories as $inventory)
                <div class="card-mantis border-l-4 border-red-500 overflow-hidden">
                    <div class="p-3">
                        <p class="font-mono text-xs text-[#2563eb] truncate">{{ $inventory->item_code }}</p>
                        <p class="font-semibold text-sm text-gray-800 dark:text-gray-100 truncate">{{ $inventory->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $inventory->category->name }}</p>

                        <div class="space-y-1 mb-2">
                            <div class="flex justify-between items-center px-2 py-1 bg-red-50 dark:bg-red-900/20 rounded text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Stock</span>
                                <span class="font-bold text-red-600 dark:text-red-400">{{ $inventory->quantity }} {{ $inventory->unit }}</span>
                            </div>
                            <div class="flex justify-between items-center px-2 py-1 bg-yellow-50 dark:bg-yellow-900/20 rounded text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Min</span>
                                <span class="font-bold text-yellow-600 dark:text-yellow-400">{{ $inventory->low_stock_threshold }} {{ $inventory->unit }}</span>
                            </div>
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400 text-center mb-2">
                            Need: <span class="font-bold text-red-600">{{ $inventory->low_stock_threshold - $inventory->quantity + 5 }} {{ $inventory->unit }}</span>
                        </div>

                        <div class="flex gap-1">
                            <a href="{{ route('admin.inventories.show', $inventory) }}" class="flex-1 text-center px-2 py-1.5 bg-[#2563eb] text-white rounded text-xs font-medium hover:bg-[#1d4ed8] transition-colors">
                                View
                            </a>
                            <a href="{{ route('admin.inventories.edit', $inventory) }}" class="flex-1 text-center px-2 py-1.5 bg-gray-600 text-white rounded text-xs font-medium hover:bg-gray-700 transition-colors">
                                Restock
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card-mantis p-16 text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-[#2563eb]/30">
                <i class="fas fa-check-circle text-white text-5xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-2">All Stocks Are Healthy</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">No items are currently below their low stock threshold.</p>
            <a href="{{ route('admin.inventories.index') }}" class="btn-mantis">
                <i class="fas fa-boxes mr-2"></i>View Inventory
            </a>
        </div>
    @endif
</div>
@endsection
