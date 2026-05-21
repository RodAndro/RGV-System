@extends('layouts.admin')

@section('title', 'Trash - Admin Dashboard')

@section('header', 'Trash')

@section('content')
<div class="p-8">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-trash-restore"></i>Deleted Records</h2>
    </div>

    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($trashable as $label => $count)
            <a href="{{ route('admin.trash.index', ['type' => \Illuminate\Support\Str::snake($label)]) }}"
               class="px-4 py-2 rounded-lg border-2 transition-all {{ $type === \Illuminate\Support\Str::snake($label) ? 'border-[#2563eb] bg-[#eff6ff] text-[#2563eb] font-semibold' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                {{ $label }}
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ $count > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500' }}">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    <div class="card-mantis overflow-hidden">
        <table class="w-full border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Name/Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Deleted At</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-gray-200">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm font-medium text-gray-800">
                            {{ $record->name ?? $record->reference_number ?? $record->request_number ?? $record->item_code ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm text-gray-500">{{ $record->deleted_at->format('M d, Y - g:i A') }}</td>
                        <td class="px-4 py-3 border-b border-gray-100 text-sm space-x-2">
                            <form action="{{ route('admin.trash.restore', ['type' => $type, 'id' => $record->id]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-[#2563eb] hover:text-[#1d4ed8] font-medium">
                                    <i class="fas fa-trash-restore mr-1"></i>Restore
                                </button>
                            </form>
                            <button @click="$dispatch('open-modal', 'force-delete-{{ $type }}-{{ $record->id }}')" type="button" class="text-red-500 hover:text-red-600 font-medium">
                                <i class="fas fa-trash mr-1"></i>Delete Permanently
                            </button>
                        </td>
                    </tr>
                    <x-confirm-delete-modal 
                        name="force-delete-{{ $type }}-{{ $record->id }}" 
                        action="{{ route('admin.trash.force-delete', ['type' => $type, 'id' => $record->id]) }}" 
                        itemName="{{ ucfirst(str_replace('_', ' ', $type)) }} #{{ $record->id }}" 
                    />
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-trash-alt text-gray-300 text-4xl mb-3 block"></i>
                            No deleted records of this type.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($records->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
