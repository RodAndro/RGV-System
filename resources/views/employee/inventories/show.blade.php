@extends('layouts.employee')

@section('title', 'Inventory Item - RGV Multi-Tech Services')

@section('header', 'Item Details')

@section('content')
<div class="p-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="card-mantis overflow-hidden mb-6">
                @if($inventory->image_path)
                    <img src="{{ asset('storage/' . $inventory->image_path) }}" alt="{{ $inventory->name }}" class="w-full h-80 object-cover">
                @else
                    <div class="w-full h-80 bg-gradient-to-br from-[#eff6ff] to-[#dbeafe] flex items-center justify-center">
                        <i class="fas fa-box text-[#2563eb] text-6xl"></i>
                    </div>
                @endif
            </div>
            <div class="card-mantis p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <p class="text-sm text-gray-500">Item Code</p>
                        <p class="text-lg font-semibold text-[#2563eb]">{{ $inventory->item_code }}</p>
                    </div>
                    <span class="badge-mantis-{{ $inventory->status == 'available' ? 'success' : ($inventory->status == 'borrowed' ? 'warning' : ($inventory->status == 'maintenance' ? 'warning' : 'danger')) }}">
                        {{ ucfirst($inventory->status) }}
                    </span>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ $inventory->name }}</h2>
                <p class="text-gray-600 mb-6">{{ $inventory->description ?? 'No description available.' }}</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Category</p>
                        <p class="text-gray-800 font-semibold">{{ $inventory->category->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Condition</p>
                        <p class="text-gray-800 font-semibold">{{ ucfirst($inventory->condition) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Quantity</p>
                        <p class="text-gray-800 font-semibold">{{ $inventory->quantity }} {{ $inventory->unit }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Added On</p>
                        <p class="text-gray-800 font-semibold">{{ $inventory->date_added?->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="card-mantis p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Actions</h3>
                <a href="{{ route('employee.borrow-requests.create', ['item' => $inventory->id]) }}" class="btn-mantis w-full inline-flex items-center justify-center py-3">
                    <i class="fas fa-hand-holding mr-2"></i>Request This Item
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
