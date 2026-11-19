<div x-data="{
    show: false,
    childCounts: {{ Js::from($counts ?? []) }},
    confirmed: false,
    open() { this.show = true; this.fetchChildCounts(); },
    async fetchChildCounts() {
        try {
            const res = await fetch('{{ $checkUrl ?? '' }}');
            this.childCounts = await res.json();
        } catch (e) {}
    }
}" x-id="['cascade-modal']">
    <button @click="open()" {{ $attributes->merge(['class' => 'text-red-500 hover:text-red-600 font-medium']) }}>
        <i class="fas fa-trash mr-1"></i>{{ $buttonText ?? 'Delete' }}
    </button>

    <template x-teleport="body">
        <div x-show="show" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
            <div @click.away="show = false" class="bg-white rounded-2xl shadow-2xl p-6 max-w-lg w-full mx-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-3">Confirm Deletion</h3>
                <p class="text-gray-600 mb-4">{{ $message ?? 'Are you sure you want to delete this record?' }}</p>

                <div x-show="Object.keys(childCounts).length > 0" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm font-medium text-red-700 mb-2">This will also delete:</p>
                    <ul class="text-sm text-red-600 space-y-1">
                        <template x-for="(count, relation) in childCounts" :key="relation">
                            <li x-show="count > 0">
                                <span x-text="count"></span>
                                <span x-text="' ' + relation.replace(/_/g, ' ')"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="mb-4">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" x-model="confirmed" class="rounded border-gray-300 text-red-500 focus:ring-red-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">I understand this action cannot be undone</span>
                    </label>
                </div>

                <div class="flex justify-end space-x-3">
                    <button @click="show = false" class="px-4 py-2 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <form method="POST" action="{{ $action ?? '' }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" :disabled="!confirmed"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Delete Permanently
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
<style>[x-cloak] { display: none !important; }</style>
