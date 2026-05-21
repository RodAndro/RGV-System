@extends('layouts.employee')

@section('title', 'New Borrow Request - RGV Multi-Tech Services')

@section('header', 'New Borrow Request')

@section('content')
<div class="p-8">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-info-circle"></i>Request Information</h2>
    </div>
    <div class="card-mantis p-8 mb-8">
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('employee.borrow-requests.store') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Borrowing *</label>
                <textarea name="reason" rows="3" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50"
                    placeholder="Explain why you need to borrow these items"></textarea>
            </div>
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Borrow Date *</label>
                    <input type="date" name="borrow_date" value="{{ old('borrow_date') }}" required min="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date *</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
                </div>
            </div>
    </div>

    <div class="section-divider"></div>

    <div class="section-header"><h2 class="section-title"><i class="fas fa-boxes"></i>Items to Borrow</h2></div>
    @php
        $oldItems = old('items', []);
        $selectedItem = request('item');
        if (empty($oldItems) && $selectedItem) {
            $oldItems = [['inventory_id' => $selectedItem, 'quantity' => '']];
        } elseif (empty($oldItems)) {
            $oldItems = [['inventory_id' => '', 'quantity' => '']];
        }
    @endphp
    <div class="card-mantis p-8 mb-8">
        <div id="items-container">
            @foreach($oldItems as $index => $oldItem)
                <div class="item-row bg-gray-50 rounded-xl p-4 mb-4 border border-gray-200" data-item-row>
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-semibold text-gray-700">Item</span>
                        <button type="button" onclick="removeItemRow(this)" class="text-red-600 hover:text-red-800 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Select Item</label>
                            <select name="items[{{ $index }}][inventory_id]" class="item-select w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-white">
                                <option value="">Choose an item</option>
                                @foreach($availableInventory as $item)
                                    <option value="{{ $item->id }}" data-max="{{ $item->quantity }}" {{ (string)($oldItem['inventory_id'] ?? '') === (string)$item->id ? 'selected' : '' }}>
                                        {{ $item->name }} (Available: {{ $item->quantity }} {{ $item->unit }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Quantity</label>
                            <input type="number" name="items[{{ $index }}][quantity]" min="1"
                                value="{{ old('items.'.$index.'.quantity', $oldItem['quantity'] ?? '') }}"
                                class="item-quantity w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-white"
                                placeholder="Enter quantity">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" onclick="addItemRow()" class="mt-2 text-[#2563eb] hover:text-[#1d4ed8] font-semibold transition-colors">
            <i class="fas fa-plus-circle mr-2"></i>Add Another Item
        </button>
    </div>

    <div class="section-divider"></div>

    <div class="flex justify-end space-x-4">
        <a href="{{ route('employee.borrow-requests.index') }}" class="px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-medium">Cancel</a>
        <button type="submit" class="btn-mantis"><i class="fas fa-paper-plane mr-2"></i>Submit Request</button>
    </div>
    </form>
</div>
@endsection

@push('scripts')
<template id="item-template">
    <div class="item-row bg-gray-50 rounded-xl p-4 mb-4 border border-gray-200">
        <div class="flex items-center justify-between mb-3">
            <span class="font-semibold text-gray-700">Item</span>
            <button type="button" onclick="removeItemRow(this)" class="text-red-600 hover:text-red-800 transition-colors"><i class="fas fa-times"></i></button>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Select Item</label>
                <select name="items[__INDEX__][inventory_id]" class="item-select w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-white">
                    <option value="">Choose an item</option>
                    @foreach($availableInventory as $item)
                        <option value="{{ $item->id }}" data-max="{{ $item->quantity }}">{{ $item->name }} (Available: {{ $item->quantity }} {{ $item->unit }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Quantity</label>
                <input type="number" name="items[__INDEX__][quantity]" min="1"
                    class="item-quantity w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-white"
                    placeholder="Enter quantity">
            </div>
        </div>
    </div>
</template>

<script>
    let itemCount = {{ count($oldItems) }};

    function addItemRow() {
        const template = document.getElementById('item-template');
        let html = template.innerHTML.replace(/__INDEX__/g, itemCount);
        const container = document.getElementById('items-container');
        const div = document.createElement('div');
        div.innerHTML = html;
        const row = div.querySelector('.item-row');
        container.appendChild(row);
        const select = row.querySelector('.item-select');
        const qtyInput = row.querySelector('.item-quantity');
        setupItemRow(select, qtyInput);
        itemCount++;
    }

    function setupItemRow(select, qtyInput) {
        select.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const maxQty = selected ? parseInt(selected.dataset.max) : 0;
            qtyInput.max = maxQty || 1;
            qtyInput.placeholder = maxQty ? 'Max: ' + maxQty : 'Enter quantity';
            validateQuantity(qtyInput);
        });
        qtyInput.addEventListener('input', function() { validateQuantity(this); });
        if (select.value) {
            const selected = select.options[select.selectedIndex];
            const maxQty = selected ? parseInt(selected.dataset.max) : 0;
            qtyInput.max = maxQty || 1;
            qtyInput.placeholder = maxQty ? 'Max: ' + maxQty : 'Enter quantity';
            validateQuantity(qtyInput);
        }
    }

    function validateQuantity(input) {
        const max = parseInt(input.max), val = parseInt(input.value);
        let error = input.parentElement.querySelector('.qty-error');
        if (val > max && max > 0) {
            if (!error) { error = document.createElement('p'); error.className = 'qty-error text-red-500 text-xs mt-1'; input.parentElement.appendChild(error); }
            error.textContent = 'Only ' + max + ' available'; input.style.borderColor = '#ef4444';
        } else {
            if (error) error.remove(); input.style.borderColor = '';
        }
    }

    function removeItemRow(button) { button.closest('.item-row').remove(); }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.item-select'), qtyInput = row.querySelector('.item-quantity');
            if (select && qtyInput) setupItemRow(select, qtyInput);
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            const rows = document.querySelectorAll('#items-container .item-row');
            let hasValidItem = false;
            let errors = [];

            rows.forEach(function(row) {
                const select = row.querySelector('.item-select');
                const qtyInput = row.querySelector('.item-quantity');
                const hasItem = select && select.value !== '';
                const hasQty = qtyInput && qtyInput.value !== '' && parseInt(qtyInput.value) > 0;

                if (!hasItem && !hasQty) {
                    row.remove();
                } else if (hasItem && hasQty) {
                    hasValidItem = true;
                } else if (hasItem && !hasQty) {
                    errors.push('Please enter a quantity for the selected item.');
                    qtyInput.style.borderColor = '#ef4444';
                } else if (!hasItem && hasQty) {
                    errors.push('Please select an item for the quantity entered.');
                    select.style.borderColor = '#ef4444';
                }
            });

            if (errors.length > 0) {
                e.preventDefault();
                alert(errors.join('\n'));
                return;
            }

            if (!hasValidItem) {
                e.preventDefault();
                alert('Please select at least one item and enter a quantity.');
            }
        });
    });
</script>
@endpush
