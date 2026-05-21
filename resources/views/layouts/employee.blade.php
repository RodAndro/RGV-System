<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('dark') === 'true' }" x-init="$watch('dark', val => { localStorage.setItem('dark', val ? 'true' : 'false'); document.documentElement.classList.toggle('dark', val); })">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Employee Dashboard - RGV Multi-Tech Services')</title>
    <script>if (localStorage.getItem('dark') === 'true') document.documentElement.classList.add('dark');</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' };</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="/css/print.css" media="print">
    <style>
        :root { --primary: #2563eb; --primary-dark: #1d4ed8; }
        [x-cloak] { display: none !important; }
        .sidebar-link-mantis { display: flex; align-items: center; padding: 11px 14px; border-radius: 10px; color: #64748b; font-weight: 500; font-size: 14.5px; transition: all 0.3s ease; cursor: pointer; }
        .sidebar-link-mantis:hover { background: rgba(37, 99, 235, 0.1); color: #2563eb; transform: translateX(4px); }
        .dark .sidebar-link-mantis { color: #94a3b8; }
        .dark .sidebar-link-mantis:hover { background: rgba(37, 99, 235, 0.15); color: #60a5fa; }
        .sidebar-link-active-mantis { padding: 11px 14px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; border-radius: 10px; font-weight: 600; font-size: 14.5px; box-shadow: 0 4px 15px rgba(37,99,235,0.3); }
        .nav-section { margin-bottom: 8px; }
        .nav-section-title { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; padding: 6px 14px; margin-top: 10px; }
        .dark .nav-section-title { color: #64748b; }
        .card-mantis { background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; transition: all 0.3s ease; }
        .card-mantis:hover { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); transform: translateY(-2px); }
        .dark .card-mantis { background: #1e293b; border-color: #334155; box-shadow: 0 1px 3px rgba(0,0,0,0.2), 0 1px 2px rgba(0,0,0,0.3); }
        .dark .card-mantis:hover { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.4), 0 2px 4px -1px rgba(0,0,0,0.3); }
        .section-divider { height: 1px; background: linear-gradient(to right, transparent, #e5e7eb, transparent); margin: 24px 0; }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #eff6ff; }
        .dark .section-header { border-bottom-color: #1e3a5f; }
        .section-title { font-size: 18px; font-weight: 700; color: #1f2937; display: flex; align-items: center; }
        .dark .section-title { color: #e2e8f0; }
        .section-title i { margin-right: 10px; color: #2563eb; }
        .badge-mantis-success { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); color: #2563eb; }
        .badge-mantis-warning { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #d97706; }
        .badge-mantis-danger { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #dc2626; }
        .btn-mantis { display: inline-flex; align-items: center; padding: 10px 20px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(37,99,235,0.3); border: none; cursor: pointer; }
        .btn-mantis:hover { background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,99,235,0.4); }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="lg:hidden fixed inset-0 bg-black/40 z-40" x-transition></div>

        <div class="fixed top-0 left-0 h-full w-72 z-50 transform transition-transform duration-300 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            @include('layouts.partials.employee-sidebar')
        </div>

        <!-- Sidebar spacer -->
        <div class="w-72 flex-shrink-0 hidden lg:block"></div>

        <main class="flex-1 min-w-0 overflow-y-auto h-screen">
            <header class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40 transition-colors duration-300">
                <div class="flex justify-between items-center px-4 md:px-8 py-4">
                    <div class="flex items-center min-w-0">
                        <button class="lg:hidden text-gray-600 dark:text-gray-400 mr-3 flex-shrink-0" @click="sidebarOpen = !sidebarOpen">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-gray-100 truncate transition-colors">@yield('header', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center space-x-2 md:space-x-4 flex-shrink-0">
                        <button @click="dark = !dark" class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-yellow-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Toggle dark mode">
                            <svg class="w-5 h-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                            <svg class="w-5 h-5 hidden dark:inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path stroke-linecap="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        </button>
                        <x-notification-bell />
                        <a href="{{ route('profile.edit') }}" class="flex items-center hover:opacity-80 transition-opacity flex-shrink-0" title="Edit Profile">
                            <div class="w-9 h-9 md:w-10 md:h-10 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-blue-500/30 flex-shrink-0">
                                {{ auth()->user()->name[0] }}
                            </div>
                            <div class="ml-2 md:ml-3 hidden md:block">
                                <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm leading-tight transition-colors">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 transition-colors">Employee</p>
                            </div>
                        </a>
                    </div>
                </div>
            </header>

            @php
                $diskTotal = null; $diskFree = null;
                try {
                    $diskTotal = disk_total_space(base_path());
                    $diskFree = disk_free_space(base_path());
                } catch (\Throwable) {}
                $storageWarning = $diskTotal && $diskFree && ($diskFree / $diskTotal) < 0.15;
            @endphp
            @if($storageWarning)
                <div class="mx-4 md:mx-8 mt-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl transition-colors">
                    <span class="text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Storage Warning:</strong> Disk usage at {{ round(100 - ($diskFree / $diskTotal * 100), 1) }}%.
                        Only {{ round($diskFree / 1024 / 1024 / 1024, 1) }} GB remaining.
                    </span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <x-toast-container />
    <x-session-timeout-warning />
    <x-unsaved-changes-warning />

    @if(session('success'))
        <script>document.addEventListener('alpine:init', () => { window.dispatchEvent(new CustomEvent('toast', { detail: { message: @json(session('success')), type: 'success' } })); });</script>
    @endif
    @if(session('error'))
        <script>document.addEventListener('alpine:init', () => { window.dispatchEvent(new CustomEvent('toast', { detail: { message: @json(session('error')), type: 'error' } })); });</script>
    @endif

    @stack('scripts')
</body>
</html>
