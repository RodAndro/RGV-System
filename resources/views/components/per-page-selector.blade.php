<div class="flex items-center gap-1.5 text-sm text-gray-500">
    <span>Show</span>
    <select onchange="window.location.href = '{!! request()->fullUrlWithQuery(['per_page' => '__VALUE__', 'page' => null]) !!}'.replace('__VALUE__', this.value)"
            class="px-3 py-1.5 pr-8 text-gray-900 border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-white appearance-none bg-[url('data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2012%2012%22%3E%3Cpath%20fill%3D%22%236b7280%22%20d%3D%22M6%208L1%203h10z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:0.65rem] bg-[right_0.5rem_center] bg-no-repeat">
        @foreach([10, 25, 50, 100] as $option)
            <option value="{{ $option }}" class="text-gray-900 dark:text-gray-900 bg-white dark:bg-white" {{ (int) request('per_page', $default ?? 20) === $option ? 'selected' : '' }}>{{ $option }}</option>
        @endforeach
    </select>
    <span>per page</span>
</div>
