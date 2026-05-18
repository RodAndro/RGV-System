@props(['name', 'action', 'itemName' => 'this record', 'method' => 'DELETE'])

<x-modal name="{{ $name }}" maxWidth="md">
    <form method="POST" action="{{ $action }}" class="p-6">
        @csrf
        @method($method)

        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Confirm Deletion</h3>
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Are you sure you want to permanently delete <strong>{{ $itemName }}</strong>? This action cannot be undone.
        </p>

        <div class="mb-4">
            <x-input-label for="password" value="Enter your password to confirm" />
            <x-text-input id="password" type="password" name="password" class="mt-1 block w-full" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" @click="$dispatch('close-modal', '{{ $name }}')"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors text-sm font-medium">
                Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                <i class="fas fa-trash mr-2"></i>Delete Permanently
            </button>
        </div>
    </form>
</x-modal>
