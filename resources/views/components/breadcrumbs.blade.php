@php
    $segments = collect(explode('/', trim(request()->path(), '/')))
        ->reject(fn($s) => in_array($s, ['admin', 'employee']));
    $breadcrumbs = [];
    $url = '';

    foreach ($segments as $segment) {
        $url .= '/' . $segment;
        $label = ucwords(str_replace(['-', '_'], ' ', $segment));

        if (is_numeric($segment)) {
            $breadcrumbs[] = ['label' => '#' . $segment, 'url' => null];
            continue;
        }

        if ($segment === 'dashboard' && count($breadcrumbs) === 0) {
            $breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')]];
            continue;
        }

        if ($segment === 'dashboard') continue;

        $breadcrumbs[] = ['label' => $label, 'url' => url($url)];
    }

    if ($segments->isEmpty() || (count($segments) === 1 && $segments->first() === 'dashboard')) {
        $breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')]];
    } elseif (empty($breadcrumbs) || $breadcrumbs[0]['label'] !== 'Dashboard') {
        array_unshift($breadcrumbs, ['label' => 'Dashboard', 'url' => route('dashboard')]);
    }
@endphp

<nav aria-label="Breadcrumb" class="px-8 pt-4">
    <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
        @foreach($breadcrumbs as $index => $crumb)
            @if($index > 0)
                <li><i class="fas fa-chevron-right text-xs text-gray-300 dark:text-gray-500"></i></li>
            @endif
            <li>
                @if($loop->last || !$crumb['url'])
                    <span class="text-gray-800 font-medium" aria-current="page">{{ $crumb['label'] }}</span>
                @else
                    <a href="{{ $crumb['url'] }}" class="hover:text-[#2563eb] transition-colors">{{ $crumb['label'] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
