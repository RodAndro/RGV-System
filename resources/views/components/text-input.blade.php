@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:border-[#2563eb] focus:ring-[#2563eb] rounded-xl shadow-sm bg-gray-50 transition-all']) }}>
