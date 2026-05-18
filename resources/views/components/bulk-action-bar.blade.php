<div x-data="{
    selected: [],
    allSelected: false,
    selectAll() {
        this.allSelected = !this.allSelected;
        this.selected = this.allSelected ? {{ Js::from($ids ?? []) }} : [];
    },
    toggle(id) {
        if (this.selected.includes(id)) {
            this.selected = this.selected.filter(i => i !== id);
        } else {
            this.selected.push(id);
        }
        this.allSelected = this.selected.length === {{ count($ids ?? []) }};
    },
    get selectedCount() { return this.selected.length; }
}" x-id="['bulk-bar']">
    <div x-show="selectedCount > 0" x-transition
        class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-600 shadow-2xl z-50 px-8 py-3">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                <span x-text="selectedCount"></span> item(s) selected
            </p>
            <div class="flex items-center space-x-3">
                <form method="POST" :action="'{{ $bulkActionUrl ?? '' }}?action=' + $refs.bulkAction.value + '&ids=' + selected.join(',')">
                    @csrf
                    <div class="flex items-center space-x-2">
                        <select x-ref="bulkAction" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                            <option value="">Choose action...</option>
                            @foreach($actions ?? [] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-mantis px-4 py-2 text-sm">
                            Apply
                        </button>
                    </div>
                </form>
                <button @click="selected = []; allSelected = false;" class="text-sm text-gray-500 hover:text-gray-700">
                    Clear selection
                </button>
            </div>
        </div>
    </div>
</div>
</div>
    </div>
</div>
