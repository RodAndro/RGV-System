@extends('layouts.admin')

@section('title', 'Inventory Report - Admin Dashboard')

@section('header', 'Inventory Report')

@section('content')
<div class="p-4 md:p-8">
    <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center text-gray-600 hover:text-[#2563eb] transition-colors text-sm mb-3">
        <i class="fas fa-arrow-left mr-1"></i>Back to Reports
    </a>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 mb-4">
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-gray-50 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-boxes text-gray-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">All</p>
                <p class="text-lg font-bold text-gray-800">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-blue-50 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check-circle text-blue-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Available</p>
                <p class="text-lg font-bold text-blue-600">{{ $stats['available'] }}</p>
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
                <p class="text-lg font-bold text-orange-600">{{ $stats['maintenance'] }}</p>
            </div>
        </div>
        <div class="card-mantis px-4 py-3 flex items-center gap-3">
            <div class="bg-red-50 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Damaged</p>
                <p class="text-lg font-bold text-red-600">{{ $stats['damaged'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-list"></i>All Inventory Data</h2>
        <div class="flex items-center gap-2">
            <x-per-page-selector />
        </div>
    </div>

    <div class="card-mantis overflow-hidden">
        <table class="w-full border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Item Code</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Quantity</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-gray-200">Condition</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventory as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm font-medium">{{ $item->item_code }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm">{{ $item->name }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm">{{ $item->category->name }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm">
                            {{ $item->quantity }}
                            @if($item->isLowStock())
                                <span class="badge-mantis-danger text-xs"> (Low Stock)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm">
                            <span class="badge-mantis-{{ $item->status == 'available' ? 'success' : ($item->status == 'borrowed' ? 'warning' : ($item->status == 'maintenance' ? 'warning' : 'danger')) }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 border-b border-gray-100 text-sm">{{ ucfirst($item->condition) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No inventory items found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($inventory->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $inventory->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<x-chatbot pageType="inventory" />
@endpush
