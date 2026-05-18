<div x-data="{ open: false, columns: {{ Js::from($columns ?? []) }}, hidden: JSON.parse(localStorage.getItem('tableColumns_' + '{{ $tableKey ?? 'default' }}') || '[]') }"
    class="relative" x-id="['col-toggle']">
    <button @click="open = !open" class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs text-gray-600 hover:bg-gray-50">
        <i class="fas fa-columns mr-1"></i>Columns
    </button>
    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-200 z-50 p-3">
        <template x-for="col in columns" :key="col.key">
            <label class="flex items-center space-x-2 py-1 cursor-pointer">
                <input type="checkbox" :checked="!hidden.includes(col.key)" @change="hidden.includes(col.key) ? hidden = hidden.filter(h => h !== col.key) : hidden.push(col.key); localStorage.setItem('tableColumns_{{ $tableKey ?? 'default' }}', JSON.stringify(hidden))"
                    class="rounded border-gray-300 text-[#74c365] focus:ring-[#74c365]">
                <span class="text-sm text-gray-700 dark:text-gray-300" x-text="col.label"></span>
            </label>
        </template>
    </div>
</div>
>
el>
        </template>
    </div>
</div>
