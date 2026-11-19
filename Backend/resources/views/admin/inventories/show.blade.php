@extends('layouts.admin')

@section('title', 'Inventory Item Details - Admin Dashboard')

@section('header', 'Inventory Item Details')

@section('content')
<div class="p-4 md:p-8">
    <a href="{{ route('admin.inventories.index') }}" class="inline-flex items-center text-gray-600 hover:text-[#2563eb] transition-colors text-sm mb-3">
        <i class="fas fa-arrow-left mr-1"></i>Back to Inventory
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Item Details -->
        <div class="lg:col-span-2 h-full">
            <div class="card-mantis p-6 h-full">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Item Code</p>
                        <p class="font-mono text-2xl font-bold text-[#2563eb]">{{ $inventory->item_code }}</p>
                    </div>
                    <div class="w-28 h-28 bg-[#eff6ff] dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-box text-[#2563eb] dark:text-blue-400 text-3xl"></i>
                    </div>
                </div>

                <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-4">{{ $inventory->name }}</h2>
                
                @if($inventory->description)
                    <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $inventory->description }}</p>
                @endif

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Category</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $inventory->category->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Quantity</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $inventory->quantity }} {{ $inventory->unit }}</p>
                        @if($inventory->isLowStock())
                            <span class="text-xs text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>Low Stock Alert</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Status</p>
                        <span class="badge-mantis-{{ $inventory->status == 'available' && $inventory->quantity > 0 ? 'success' : ($inventory->status == 'borrowed' || $inventory->status == 'maintenance' ? 'warning' : 'danger') }}">
                            {{ $inventory->status === 'available' && $inventory->quantity <= 0 ? 'Out of Stock' : ucfirst($inventory->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Condition</p>
                        <span class="badge-mantis-{{ $inventory->condition == 'new' ? 'info' : ($inventory->condition == 'good' ? 'success' : ($inventory->condition == 'fair' ? 'warning' : 'danger')) }}">
                            {{ ucfirst($inventory->condition) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Date Added</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $inventory->date_added->format('F d, Y') }}</p>
                    </div>
                </div>

            </div>


        </div>

        <!-- Info Panel -->
        <div class="lg:pt-0 flex h-full flex-col gap-4">
            <div class="card-mantis p-5 flex-1">
                <h3 class="text-[20px] font-extrabold text-gray-800 dark:text-gray-100 mb-4">Stock Information</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-[#eff6ff] dark:bg-blue-900/20 rounded-xl border border-[#2563eb]/20 dark:border-blue-700 text-[15px]">
                        <span class="text-gray-700 dark:text-gray-300">Current Stock</span>
                        <span class="font-bold text-[#2563eb] dark:text-blue-400">{{ $inventory->quantity }} {{ $inventory->unit }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-800 text-[15px]">
                        <span class="text-gray-700 dark:text-gray-300">Low Stock Threshold</span>
                        <span class="font-bold text-yellow-600 dark:text-yellow-400">{{ $inventory->low_stock_threshold }} {{ $inventory->unit }}</span>
                    </div>
                    @if($inventory->isLowStock())
                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
                            <p class="text-red-800 dark:text-red-300 font-bold text-center text-[16px]">
                                <i class="fas fa-exclamation-triangle mr-2 text-[16px]"></i>Low Stock Alert
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card-mantis p-4 flex-1">
                <h3 class="text-center text-[20px] font-extrabold text-gray-800 dark:text-gray-100 mb-3">Quick Actions</h3>
                <div class="space-y-2.5 flex flex-col items-center">
                    <a href="{{ route('admin.inventories.qrcode', ['token' => $inventory->qr_token]) }}" class="flex w-full max-w-[620px] items-center justify-center gap-2 rounded-[12px] bg-[#dfeee3] px-3 py-2.5 text-center text-[16px] font-semibold text-[#1f7a55] shadow-sm transition-all hover:bg-[#d2ead9]">
                        <i class="fas fa-qrcode text-[24px]"></i>
                        <span>Download QR Code</span>
                        <span class="text-[16px]">(JPEG)</span>
                    </a>
                    <a href="{{ route('admin.inventories.edit', $inventory) }}" class="flex w-full max-w-[620px] items-center justify-center gap-2 rounded-[12px] bg-[#0f7aa3] px-3 py-2.5 text-center text-[16px] font-bold text-white shadow-sm transition-all hover:bg-[#0d6f96]">
                        <i class="fas fa-edit text-[20px]"></i>
                        <span>Edit Item</span>
                    </a>
                    <a href="{{ route('admin.inventories.index') }}" class="flex w-full max-w-[620px] items-center justify-center gap-2 rounded-[12px] bg-[#3a4d63] px-3 py-2.5 text-center text-[16px] font-bold text-white shadow-sm transition-all hover:bg-[#2f4257]">
                        <i class="fas fa-list text-[20px]"></i>
                        <span>View All Items</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div class="card-mantis p-6 w-full">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Borrow History</h3>
                @if($inventory->borrowItems->count() > 0)
                    <div class="space-y-3">
                        @foreach($inventory->borrowItems as $borrowItem)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $borrowItem->borrowRequest->request_number }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $borrowItem->borrowRequest->employee?->name ?? 'N/A' }} • 
                                        {{ $borrowItem->borrowRequest->borrow_date->format('M d, Y') }}
                                    </p>
                                </div>
                                <span class="badge-mantis-{{ $borrowItem->is_returned ? 'success' : 'warning' }}">
                                    {{ $borrowItem->is_returned ? 'Returned' : 'Borrowed' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400 py-4">No borrow history</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
