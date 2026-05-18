<div x-data="{ open: false }" class="relative">
    <button @click="open = !open"
        class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 flex items-center space-x-2">
        <i class="fas fa-filter"></i>
        <span>Filters {{ request()->hasAny(array_keys($filters ?? [])) ? '(' . count(array_filter(request()->only(array_keys($filters ?? [])))) . ')' : '' }}</span>
        <i class="fas fa-chevron-down text-xs" :class="{ 'rotate-180': open }"></i>
    </button>

    <div x-show="open" @click.away="open = false" x-transition
        class="absolute left-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-50 p-4">
        <form method="GET" action="{{ $action ?? request()->url() }}">
            @foreach($filters ?? [] as $name => $filter)
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ $filter['label'] ?? ucfirst($name) }}</label>
                    @if(($filter['type'] ?? 'text') === 'select')
                        <select name="{{ $name }}"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#74c365]">
                            <option value="">All</option>
                            @foreach($filter['options'] ?? [] as $value => $label)
                                <option value="{{ $value }}" {{ request($name) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    @elseif(($filter['type'] ?? 'text') === 'date')
                        <input type="date" name="{{ $name }}" value="{{ request($name) }}"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#74c365]">
                    @else
                        <input type="text" name="{{ $name }}" value="{{ request($name) }}"
                            placeholder="{{ $filter['placeholder'] ?? 'Search...' }}"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#74c365]">
                    @endif
                </div>
            @endforeach

            <div class="flex justify-between pt-2 border-t border-gray-100">
                <a href="{{ request()->url() }}" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 py-2">Clear all</a>
                <button type="submit" class="btn-mantis px-4 py-1.5 text-sm">Apply</button>
            </div>
        </form>
    </div>
</div>
