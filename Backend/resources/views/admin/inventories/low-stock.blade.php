@extends('layouts.admin')

@section('title', 'Low Stock Alerts - Admin Dashboard')

@section('header', 'Low Stock Alerts')

@section('content')
<div class="p-4 md:p-8">
    <div class="mb-4">
        <a href="{{ route('admin.inventories.index') }}" class="inline-flex items-center gap-2 text-[17px] font-semibold text-gray-700 hover:text-[#2563eb] transition-colors">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Inventory</span>
        </a>
    </div>

    <div class="mb-6 rounded-[10px] border border-[#f3b6b6] bg-[#f9e7e7] px-5 py-4 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#e53935] text-white shadow-sm">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <div class="leading-tight">
                <h3 class="text-[20px] font-bold text-[#1f2937]">Low Stock Alert</h3>
                <p class="mt-1 text-[16px] font-medium text-[#dc2626]">{{ $inventories->count() }} item(s) below threshold</p>
            </div>
        </div>
    </div>

    @if($inventories->count() > 0)
        <div class="card-mantis overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gradient-to-r from-[#eff6ff] to-white dark:from-gray-800 dark:to-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Item Code</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Threshold</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inventories as $inventory)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                    <span class="font-mono text-xs font-semibold text-[#2563eb]">{{ $inventory->item_code }}</span>
                                </td>
                                <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">{{ $inventory->name }}</p>
                                </td>
                                <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">
                                    {{ $inventory->category?->name ?? 'Uncategorized' }}
                                </td>
                                <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">{{ $inventory->quantity }} {{ $inventory->unit }}</p>
                                </td>
                                <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                    <span class="font-semibold text-sm text-yellow-600 dark:text-yellow-400">{{ $inventory->low_stock_threshold }} {{ $inventory->unit }}</span>
                                </td>
                                <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                    <span class="low-stock-alert"><i class="fas fa-exclamation-triangle"></i>Low Stock</span>
                                </td>
                                <td class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.inventories.show', $inventory) }}" class="w-9 h-9 bg-[#eff6ff] text-[#2563eb] rounded-lg flex items-center justify-center hover:bg-[#dbeafe] transition-colors" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.inventories.edit', $inventory) }}" class="w-9 h-9 bg-yellow-100 text-yellow-600 rounded-lg flex items-center justify-center hover:bg-yellow-200 transition-colors" title="Restock">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
