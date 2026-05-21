@extends('layouts.admin')

@section('title', 'Edit Inventory Item - Admin Dashboard')

@section('header', 'Edit Inventory Item')

@section('content')
<div class="p-4 md:p-8">
    <div class="card-mantis p-6 md:p-8">
        @if(session('success'))
            <div class="bg-gradient-to-r from-[#eff6ff] to-[#dbeafe] dark:from-blue-900/20 dark:to-blue-800/10 border border-[#2563eb] dark:border-blue-700 text-[#1e40af] dark:text-blue-300 px-4 py-3 rounded-xl mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.inventories.update', $inventory) }}" method="POST" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item Code *</label>
                    <input type="text" name="item_code" value="{{ old('item_code', $inventory->item_code) }}" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item Name *</label>
                    <input type="text" name="name" value="{{ old('name', $inventory->name) }}" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category *</label>
                    <select name="category_id" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $inventory->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Supplier</label>
                    <select name="supplier_id"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                        <option value="">Select supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ $inventory->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quantity *</label>
                    <input type="number" name="quantity" value="{{ old('quantity', $inventory->quantity) }}" required min="0"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Unit *</label>
                    <select name="unit" id="unitSelect" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                        <option value="pcs" {{ $inventory->unit == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                        <option value="kg" {{ $inventory->unit == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                        <option value="lbs" {{ $inventory->unit == 'lbs' ? 'selected' : '' }}>Pounds (lbs)</option>
                        <option value="meters" {{ $inventory->unit == 'meters' ? 'selected' : '' }}>Meters</option>
                        <option value="liters" {{ $inventory->unit == 'liters' ? 'selected' : '' }}>Liters</option>
                        <option value="boxes" {{ $inventory->unit == 'boxes' ? 'selected' : '' }}>Boxes</option>
                        <option value="sets" {{ $inventory->unit == 'sets' ? 'selected' : '' }}>Sets</option>
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Unit is automatically suggested based on the selected category</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Unit Cost</label>
                    <input type="number" name="unit_cost" value="{{ old('unit_cost', $inventory->unit_cost) }}" min="0" step="0.01"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Low Stock Threshold *</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $inventory->low_stock_threshold) }}" required min="0"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status *</label>
                    <select name="status" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                        <option value="available" {{ $inventory->status == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="borrowed" {{ $inventory->status == 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                        <option value="maintenance" {{ $inventory->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="damaged" {{ $inventory->status == 'damaged' ? 'selected' : '' }}>Damaged</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Condition *</label>
                    <select name="condition" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                        <option value="new" {{ $inventory->condition == 'new' ? 'selected' : '' }}>New</option>
                        <option value="good" {{ $inventory->condition == 'good' ? 'selected' : '' }}>Good</option>
                        <option value="fair" {{ $inventory->condition == 'fair' ? 'selected' : '' }}>Fair</option>
                        <option value="poor" {{ $inventory->condition == 'poor' ? 'selected' : '' }}>Poor</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Added *</label>
                    <input type="date" name="date_added" value="{{ old('date_added', $inventory->date_added->format('Y-m-d')) }}" required
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location</label>
                    <input type="text" name="location" value="{{ old('location', $inventory->location) }}"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-100">{{ old('description', $inventory->description) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item Image</label>
                    @if($inventory->image_path)
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Current Image:</p>
                            <img src="{{ asset('storage/' . $inventory->image_path) }}" class="w-32 h-32 object-cover rounded-xl">
                        </div>
                    @endif
                    <div class="border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-xl p-6 text-center hover:border-[#2563eb] transition bg-gray-50 dark:bg-gray-800">
                        <input type="file" name="image" accept=".jpg,.jpeg,.png"
                            class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#eff6ff] dark:file:bg-blue-900/30 file:text-[#2563eb] dark:file:text-blue-400 hover:file:bg-[#dbeafe]">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Accepted formats: JPG, PNG (Max 5MB)</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="{{ route('admin.inventories.index') }}" class="px-6 py-3 border-2 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-all font-medium">
                    Cancel
                </a>
                <button type="submit" class="btn-mantis">
                    <i class="fas fa-save mr-2"></i>Update Item
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const categoryUnits = @json($categoryUnits);
    const unitLabels = {
        'pcs': 'Pieces (pcs)', 'kg': 'Kilograms (kg)', 'lbs': 'Pounds (lbs)', 'meters': 'Meters', 'liters': 'Liters',
        'boxes': 'Boxes', 'sets': 'Sets', 'licenses': 'Licenses', 'subscriptions': 'Subscriptions', 'reams': 'Reams',
        'packets': 'Packets', 'bottles': 'Bottles', 'containers': 'Containers',
    };

    function updateUnitOptions() {
        const categorySelect = document.querySelector('select[name="category_id"]');
        const unitSelect = document.getElementById('unitSelect');
        const selectedCategoryId = categorySelect.value;
        if (!selectedCategoryId || !categoryUnits[selectedCategoryId]) {
            const allUnits = ['pcs', 'kg', 'lbs', 'meters', 'liters', 'boxes', 'sets', 'licenses', 'subscriptions', 'reams', 'packets', 'bottles', 'containers'];
            populateUnitSelect(allUnits);
            return;
        }
        populateUnitSelect(categoryUnits[selectedCategoryId]);
    }

    function populateUnitSelect(units) {
        const unitSelect = document.getElementById('unitSelect');
        const currentValue = unitSelect.value;
        unitSelect.innerHTML = '';
        units.forEach(unit => {
            const option = document.createElement('option');
            option.value = unit;
            option.textContent = unitLabels[unit] || unit;
            option.selected = (currentValue === unit);
            unitSelect.appendChild(option);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.querySelector('select[name="category_id"]');
        if (categorySelect) {
            updateUnitOptions();
            categorySelect.addEventListener('change', updateUnitOptions);
        }
    });
</script>
@endpush
