@extends('layouts.admin')

@section('title', 'Trash - Admin Dashboard')

@section('header', 'Trash')

@section('content')
<div class="p-8" x-data="{ selectedIds: {{ Js::from(old('ids', [])) }} }">
    <form id="bulk-force-delete-form" action="{{ route('admin.trash.force-delete-selected', ['type' => $type]) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

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

    <div x-show="selectedIds.length > 0" x-cloak class="flex flex-wrap items-center justify-between gap-3 mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
        <p class="text-sm font-medium text-red-800">
            <span x-text="selectedIds.length"></span>
            <span x-text="selectedIds.length === 1 ? 'record' : 'records'"></span> selected
        </p>
        <div class="flex items-center gap-3">
            <button type="button" @click="$dispatch('open-modal', 'bulk-force-delete')" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                <i class="fas fa-trash mr-2"></i>Delete Selected Permanently
            </button>
            <button type="button" @click="selectedIds = []" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                Clear selection
            </button>
        </div>
    </div>

    <div class="card-mantis overflow-hidden">
        <table class="w-full border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-12 px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">
                        <input type="checkbox"
                               aria-label="Select all records on this page"
                               :checked="selectedIds.length === {{ $records->count() }} && selectedIds.length > 0"
                               @change="selectedIds = $event.target.checked ? {{ Js::from($records->getCollection()->pluck('id')->map(fn ($id) => (string) $id)->all()) }} : []"
                               class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Name/Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Deleted At</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-gray-200">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 border-b border-r border-gray-100">
                            <input type="checkbox"
                                   name="ids[]"
                                   value="{{ $record->id }}"
                                   form="bulk-force-delete-form"
                                   aria-label="Select record {{ $record->id }}"
                                   x-model="selectedIds"
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        </td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm font-medium text-gray-800">
                            {{ $record->name ?? $record->reference_number ?? $record->request_number ?? $record->item_code ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                        <td class="px-4 py-3 border-b border-r border-gray-100 text-sm text-gray-500">{{ $record->deleted_at->format('M d, Y - g:i A') }}</td>
                        <td class="px-4 py-3 border-b border-gray-100 text-sm">
                            <div class="flex flex-wrap items-center gap-2">
                            <form action="{{ route('admin.trash.restore', ['type' => $type, 'id' => $record->id]) }}" method="POST" class="inline-flex">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-blue-200 bg-blue-50 text-[#2563eb] hover:bg-blue-100 hover:text-[#1d4ed8] rounded-lg font-medium transition-colors">
                                    <i class="fas fa-trash-restore mr-1"></i>Restore
                                </button>
                            </form>
                            <button @click="$dispatch('open-modal', 'force-delete-{{ $type }}-{{ $record->id }}')" type="button" class="inline-flex items-center px-3 py-1.5 border border-red-200 bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 rounded-lg font-medium transition-colors">
                                <i class="fas fa-trash mr-1"></i>Delete Permanently
                            </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fas fa-trash-alt text-gray-300 text-4xl"></i>
                                <span>No deleted records of this type.</span>
                            </div>
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
    @foreach($records as $record)
        <x-confirm-delete-modal
            name="force-delete-{{ $type }}-{{ $record->id }}"
            action="{{ route('admin.trash.force-delete', ['type' => $type, 'id' => $record->id]) }}"
            itemName="{{ ucfirst(str_replace('_', ' ', $type)) }} #{{ $record->id }}"
        />
    @endforeach

    <x-modal name="bulk-force-delete" maxWidth="md" :show="$errors->has('password') || $errors->has('ids')">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Confirm Permanent Deletion</h3>
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Permanently delete <strong x-text="selectedIds.length"></strong>
                <span x-text="selectedIds.length === 1 ? 'record' : 'records'"></span>? This action cannot be undone.
            </p>

            <div class="mb-4">
                <x-input-label for="bulk-delete-password" value="Enter your password to confirm" />
                <x-text-input id="bulk-delete-password" type="password" name="password" form="bulk-force-delete-form" class="mt-1 block w-full" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                <x-input-error :messages="$errors->get('ids')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal', 'bulk-force-delete')" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors text-sm font-medium">
                    Cancel
                </button>
                <button type="submit" form="bulk-force-delete-form" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                    <i class="fas fa-trash mr-2"></i>Delete <span x-text="selectedIds.length"></span> Permanently
                </button>
            </div>
        </div>
    </x-modal>
</div>
@endsection
