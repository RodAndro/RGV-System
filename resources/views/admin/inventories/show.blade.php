@extends('layouts.admin')

@section('title', 'Inventory Item Details - Admin Dashboard')

@section('header', 'Inventory Item Details')

@section('content')
<div class="p-4 md:p-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Item Details -->
        <div class="lg:col-span-2">
            <div class="card-mantis p-6 mb-6">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Item Code</p>
                        <p class="font-mono text-2xl font-bold text-[#2563eb]">{{ $inventory->item_code }}</p>
                    </div>
                    @if($inventory->image_path)
                        <img src="{{ asset('storage/' . $inventory->image_path) }}" class="w-32 h-32 object-cover rounded-xl">
                    @else
                        <div class="w-32 h-32 bg-[#eff6ff] dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-box text-[#2563eb] dark:text-blue-400 text-4xl"></i>
                        </div>
                    @endif
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
                        <span class="badge-mantis-{{ $inventory->status == 'available' ? 'success' : ($inventory->status == 'borrowed' ? 'warning' : ($inventory->status == 'maintenance' ? 'warning' : 'danger')) }}">
                            {{ ucfirst($inventory->status) }}
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

                <div class="mt-6 flex space-x-4">
                    <a href="{{ route('admin.inventories.edit', $inventory) }}" class="btn-mantis">
                        <i class="fas fa-edit mr-2"></i>Edit Item
                    </a>
                    <form action="{{ route('admin.inventories.destroy', $inventory) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-6 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium">
                            <i class="fas fa-trash mr-2"></i>Delete Item
                        </button>
                    </form>
                </div>
            </div>

            <!-- Borrow History -->
            <div class="card-mantis p-6">
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

        <!-- Info Panel -->
        <div>
            <div class="card-mantis p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Stock Information</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-[#eff6ff] dark:bg-blue-900/20 rounded-xl border border-[#2563eb]/20 dark:border-blue-700">
                        <span class="text-gray-700 dark:text-gray-300">Current Stock</span>
                        <span class="font-bold text-[#2563eb] dark:text-blue-400">{{ $inventory->quantity }} {{ $inventory->unit }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-800">
                        <span class="text-gray-700 dark:text-gray-300">Low Stock Threshold</span>
                        <span class="font-bold text-yellow-600 dark:text-yellow-400">{{ $inventory->low_stock_threshold }} {{ $inventory->unit }}</span>
                    </div>
                    @if($inventory->isLowStock())
                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
                            <p class="text-red-800 dark:text-red-300 font-semibold text-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Low Stock Alert
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card-mantis p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.inventories.edit', $inventory) }}" class="block text-center btn-mantis">
                        <i class="fas fa-edit mr-2"></i>Edit Item
                    </a>
                    <a href="{{ route('admin.inventories.index') }}" class="block text-center px-4 py-3 bg-gray-700 dark:bg-gray-800 text-white rounded-xl hover:bg-gray-800 dark:hover:bg-gray-700 transition-all font-medium">
                        <i class="fas fa-list mr-2"></i>View All Items
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
