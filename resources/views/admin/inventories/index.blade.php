@extends('layouts.admin')

@section('title', 'Inventory Management - RGV Multi-Tech Services')

@section('header', 'Inventory Management')

@section('content')
<div class="p-8" x-data="{ showImportModal: false }">
                <!-- Filters Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-filter"></i>Filter Inventory</h2>
                </div>
                <div class="card-mantis p-6 mb-8">
                    <form method="GET" action="{{ route('admin.inventories.index') }}">
                        <div class="flex flex-wrap gap-4">
                            <select name="category_id" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                                <option value="">All Status</option>
                                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="damaged" {{ request('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                            </select>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search items..." class="flex-1 px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400">
                            <button type="submit" class="btn-mantis px-6">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                            <a href="{{ route('admin.inventories.index') }}" class="btn-mantis-outline px-6">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>

                <div class="section-divider"></div>

                <div class="section-divider"></div>
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-boxes"></i>All Inventory Items</h2>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $inventories->total() }} items</span>
                        <x-per-page-selector />
                        <button @click="showImportModal = true" class="px-3 py-1.5 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-[#74c365] transition-colors">
                            <i class="fas fa-upload mr-1"></i>Import
                        </button>
                        <a href="{{ route('admin.import-export.inventory.export', request()->query() + ['format' => 'xlsx']) }}" class="px-3 py-1.5 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-[#74c365] transition-colors">
                            <i class="fas fa-download mr-1"></i>Export
                        </a>
                    </div>
                </div>
                <div class="card-mantis overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-[#f0f9ef] to-white dark:from-gray-800 dark:to-gray-900">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Item Code</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Name</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Category</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Quantity</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Condition</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($inventories as $inventory)
                                    <tr class="hover:bg-[#f0f9ef]/50 dark:hover:bg-gray-800/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-mono text-sm font-semibold text-[#74c365]">{{ $inventory->item_code }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $inventory->name }}</p>
                                            @if($inventory->image_path)
                                                <img src="{{ asset('storage/' . $inventory->image_path) }}" class="w-10 h-10 object-cover rounded-xl mt-2">
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $inventory->category->name }}</td>
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $inventory->quantity }} {{ $inventory->unit }}</p>
                                            @if($inventory->isLowStock())
                                                <span class="badge-mantis-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Low Stock Alert</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="badge-mantis-{{ $inventory->status == 'available' ? 'success' : ($inventory->status == 'borrowed' ? 'warning' : ($inventory->status == 'maintenance' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($inventory->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="badge-mantis-{{ $inventory->condition == 'new' ? 'success' : ($inventory->condition == 'good' ? 'success' : ($inventory->condition == 'fair' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($inventory->condition) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.inventories.show', $inventory) }}" class="w-9 h-9 bg-[#f0f9ef] text-[#74c365] rounded-lg flex items-center justify-center hover:bg-[#e0f3df] transition-colors" title="View">
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
                                            <div class="w-16 h-16 bg-[#f0f9ef] dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                                <i class="fas fa-box-open text-[#74c365] text-2xl"></i>
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
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[#f0f9ef] dark:bg-green-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                        <i class="fas fa-file-import text-[#74c365]"></i>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-gray-100" id="import-modal-title">Import Inventory Items</h3>
                                        <div class="mt-4">
                                            <form method="POST" action="{{ route('admin.inventories.import') }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="space-y-4">
                                                    <div>
                                                        <label for="import-file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload CSV or XLSX File</label>
                                                        <input type="file" name="file" id="import-file" accept=".csv,.xlsx,.xls" required class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#f0f9ef] dark:file:bg-gray-800 file:text-[#74c365] dark:file:text-green-400 hover:file:bg-[#e0f3df] dark:hover:file:bg-gray-700">
                                                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Accepted formats: CSV, XLSX, XLS. Max size: 50MB.</p>
                                                    </div>
                                                    <div>
                                                        <label for="duplicate_strategy" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duplicate Handling</label>
                                                        <select name="duplicate_strategy" id="duplicate_strategy" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] bg-gray-50 dark:bg-gray-800 dark:text-gray-100 text-sm">
                                                            <option value="skip">Skip duplicates (keep existing)</option>
                                                            <option value="update">Update existing items</option>
                                                        </select>
                                                    </div>
                                                    <div class="bg-[#f0f9ef] dark:bg-green-900/20 rounded-lg p-3 text-sm text-gray-600 dark:text-gray-400">
                                                        <p class="font-medium text-[#468a3f] dark:text-green-400 mb-1"><i class="fas fa-info-circle mr-1"></i>Template</p>
                                                        <p class="mb-2">Download the import template for the correct column format.</p>
                                                        <a href="{{ route('admin.inventories.import-template') }}" class="inline-flex items-center text-[#74c365] font-medium hover:text-[#5dad4f]">
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
