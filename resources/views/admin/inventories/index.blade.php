@extends('layouts.admin')

@section('title', 'Inventory Management - RGV Multi-Tech Services')

@section('header', 'Inventory Management')

@section('content')
<div class="p-8" x-data="{ showImportModal: false }">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 mb-4">
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-gray-50 dark:bg-gray-800 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-boxes text-gray-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">All</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $inventories->total() }}</p>
                        </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-blue-50 dark:bg-blue-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check-circle text-blue-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Available</p>
                            <p class="text-lg font-bold text-blue-600">{{ $stats['available'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-yellow-50 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-hand-holding text-yellow-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Borrowed</p>
                            <p class="text-lg font-bold text-yellow-600">{{ $stats['borrowed'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-orange-50 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-wrench text-orange-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Maintenance</p>
                            <p class="text-lg font-bold text-orange-600">{{ $stats['maintenance'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="card-mantis px-4 py-3 flex items-center gap-3">
                        <div class="bg-red-50 dark:bg-red-900/30 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 truncate">Damaged</p>
                            <p class="text-lg font-bold text-red-600">{{ $stats['damaged'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-filter"></i>Filter</h2>
                </div>
                <div class="card-mantis p-4 mb-6">
                    <form method="GET" action="{{ route('admin.inventories.index') }}">
                        <div class="flex flex-wrap items-center gap-2">
                            <select name="category_id" class="pl-3 pr-8 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="pl-3 pr-8 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                                <option value="">All Status</option>
                                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="damaged" {{ request('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                            </select>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search items..." class="flex-1 px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400">
                            <button type="submit" class="btn-mantis px-4 py-2 text-sm">
                                <i class="fas fa-search mr-1"></i>Search
                            </button>
                            <a href="{{ route('admin.inventories.index') }}" class="btn-mantis-outline px-4 py-2 text-sm">
                                Clear
                            </a>
                            <a href="{{ route('admin.inventories.low-stock') }}" class="px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg text-xs font-medium hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors flex items-center">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Low Stock Alerts
                            </a>
                        </div>
                    </form>
                </div>

                <div class="section-divider"></div>
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-boxes"></i>All Inventory Items</h2>
                    <div class="flex items-center gap-2">
                        <x-per-page-selector />
                        <button @click="showImportModal = true" class="px-3 py-1.5 border border-[#2563eb] rounded-lg text-xs text-[#2563eb] hover:bg-[#eff6ff] dark:text-[#2563eb] dark:border-[#2563eb] dark:hover:bg-blue-900/20 transition-colors">
                            <i class="fas fa-upload mr-1"></i>Import
                        </button>
                        <a href="{{ route('admin.import-export.inventory.export', request()->query() + ['format' => 'xlsx']) }}" class="px-3 py-1.5 border border-[#2563eb] rounded-lg text-xs text-[#2563eb] hover:bg-[#eff6ff] dark:text-[#2563eb] dark:border-[#2563eb] dark:hover:bg-blue-900/20 transition-colors">
                            <i class="fas fa-download mr-1"></i>Export
                        </a>
                        <a href="{{ route('admin.inventories.create') }}" class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-medium hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                            <i class="fas fa-plus mr-1"></i>Add New Item
                        </a>
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
                                            <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">{{ $inventory->name }}</p>
                                            @if($inventory->image_path)
                                                <img src="{{ asset('storage/' . $inventory->image_path) }}" class="w-8 h-8 object-cover rounded mt-1">
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">{{ $inventory->category->name }}</td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                            <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">{{ $inventory->quantity }} {{ $inventory->unit }}</p>
                                            @if($inventory->isLowStock())
                                                <span class="badge-mantis-danger text-xs"><i class="fas fa-exclamation-triangle mr-1"></i>Low Stock Alert</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                            <span class="badge-mantis-{{ $inventory->status == 'available' ? 'success' : ($inventory->status == 'borrowed' ? 'warning' : ($inventory->status == 'maintenance' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($inventory->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 border-b border-r border-gray-100 dark:border-gray-700">
                                            <span class="badge-mantis-{{ $inventory->condition == 'new' ? 'success' : ($inventory->condition == 'good' ? 'success' : ($inventory->condition == 'fair' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($inventory->condition) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.inventories.show', $inventory) }}" class="w-9 h-9 bg-[#eff6ff] text-[#2563eb] rounded-lg flex items-center justify-center hover:bg-[#dbeafe] transition-colors" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.inventories.edit', $inventory) }}" class="w-9 h-9 bg-yellow-100 text-yellow-600 rounded-lg flex items-center justify-center hover:bg-yellow-200 transition-colors" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button @click="$dispatch('open-modal', 'delete-inventory-{{ $inventory->id }}')" type="button" class="w-9 h-9 bg-red-100 text-red-600 rounded-lg flex items-center justify-center hover:bg-red-200 transition-colors" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <x-confirm-delete-modal 
                                        name="delete-inventory-{{ $inventory->id }}" 
                                        action="{{ route('admin.inventories.destroy', $inventory) }}" 
                                        itemName="'{{ addslashes($inventory->name) }}'" 
                                    />
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <div class="w-16 h-16 bg-[#eff6ff] dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                                <i class="fas fa-box-open text-[#2563eb] text-2xl"></i>
                                            </div>
                                            <p class="font-medium">No inventory items found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($inventories->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                            {{ $inventories->links() }}
                        </div>
                    @endif
                </div>

                <!-- Import Modal -->
                <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="import-modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showImportModal = false" aria-hidden="true"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[#eff6ff] dark:bg-blue-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                        <i class="fas fa-file-import text-[#2563eb]"></i>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-gray-100" id="import-modal-title">Import Inventory Items</h3>
                                        <div class="mt-4">
                                            <form method="POST" action="{{ route('admin.inventories.import') }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="space-y-4">
                                                    <div>
                                                        <label for="import-file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload CSV or XLSX File</label>
                                                        <input type="file" name="file" id="import-file" accept=".csv,.xlsx,.xls" required class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#eff6ff] dark:file:bg-gray-800 file:text-[#2563eb] dark:file:text-blue-400 hover:file:bg-[#dbeafe] dark:hover:file:bg-gray-700">
                                                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Accepted formats: CSV, XLSX, XLS. Max size: 50MB.</p>
                                                    </div>
                                                    <div>
                                                        <label for="duplicate_strategy" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duplicate Handling</label>
                                                        <select name="duplicate_strategy" id="duplicate_strategy" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] bg-gray-50 dark:bg-gray-800 dark:text-gray-100 text-sm">
                                                            <option value="skip">Skip duplicates (keep existing)</option>
                                                            <option value="update">Update existing items</option>
                                                        </select>
                                                    </div>
                                                    <div class="bg-[#eff6ff] dark:bg-blue-900/20 rounded-lg p-3 text-sm text-gray-600 dark:text-gray-400">
                                                        <p class="font-medium text-[#1e40af] dark:text-blue-400 mb-1"><i class="fas fa-info-circle mr-1"></i>Template</p>
                                                        <p class="mb-2">Download the import template for the correct column format.</p>
                                                        <a href="{{ route('admin.inventories.import-template') }}" class="inline-flex items-center text-[#2563eb] font-medium hover:text-[#1d4ed8]">
                                                            <i class="fas fa-download mr-1"></i>Download Template (CSV)
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="mt-5 sm:mt-4 flex gap-3 justify-end">
                                                    <button type="button" @click="showImportModal = false" class="px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="btn-mantis px-6 text-sm">
                                                        <i class="fas fa-upload mr-2"></i>Queue Import
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
@endsection
