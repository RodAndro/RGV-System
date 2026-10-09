<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('dark') === 'true' }" x-init="$watch('dark', val => { localStorage.setItem('dark', val ? 'true' : 'false'); document.documentElement.classList.toggle('dark', val); })">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard - RGV Multi-Tech Services')</title>
    <script>if (localStorage.getItem('dark') === 'true') document.documentElement.classList.add('dark');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="/css/print.css" media="print">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
        }
        [x-cloak] { display: none !important; }
        .sidebar-link-mantis {
            display: flex;
            align-items: center;
            padding: 11px 14px;
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.82);
            font-weight: 500;
            font-size: 14.5px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .sidebar-link-mantis:hover {
            background: rgba(255, 255, 255, 0.12);
            color: white;
            transform: translateX(4px);
        }
        .dark .sidebar-link-mantis { color: rgba(255, 255, 255, 0.82); }
        .dark .sidebar-link-mantis:hover {
            background: rgba(255, 255, 255, 0.12);
            color: white;
        }
        .sidebar-link-active-mantis {
            display: flex;
            align-items: center;
            padding: 11px 14px;
            background: #0a5c83;
            color: white;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14.5px;
            box-shadow: 0 4px 15px rgba(10, 92, 131, 0.35);
        }
        .dropdown-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            padding-left: 48px;
        }
        .dropdown-menu.open {
            max-height: 500px;
            transition: max-height 0.3s ease-in;
        }
        .dropdown-item {
            padding: 7px 14px;
            color: #ffffff !important;
            font-weight: 500;
            font-size: 13px;
            border-radius: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
            display: block;
        }
        .dropdown-item:hover {
            background: rgba(37, 99, 235, 0.08);
            color: #2563eb;
        }
        .dark .dropdown-item { color: #94a3b8; }
        .dark .dropdown-item:hover {
            background: rgba(37, 99, 235, 0.15);
            color: #60a5fa;
        }
        .chevron-icon {
            transition: transform 0.3s ease;
        }
        .chevron-icon.rotate {
            transform: rotate(180deg);
        }
        .nav-section {
            margin-bottom: 8px;
        }
        .nav-section-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            padding: 6px 14px;
            margin-top: 10px;
        }
        .dark .nav-section-title { color: #ffffff !important; }
        .card-mantis {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        .dark .card-mantis {
            background: #1e293b;
            border-color: #334155;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2), 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        .dark .card-mantis:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.4), 0 2px 4px -1px rgba(0, 0, 0, 0.3);
        }
        .card-mantis:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }
        .section-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e5e7eb, transparent);
            margin: 24px 0;
        }
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #d8e6f3;
        }
        .dark .section-header { border-bottom-color: #1e3a5f; }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
        }
        .dark .section-title { color: #e2e8f0; }
        .section-title i {
            margin-right: 10px;
            color: #0f6f9d;
        }
        .badge-mantis-success {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #2563eb;
        }
        .badge-mantis-warning {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }
        .badge-mantis-danger {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }
        .badge-mantis-info {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #2563eb;
        }
        .btn-mantis {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: #0f6f9d;
            color: white;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(15, 111, 157, 0.3);
        }
        .btn-mantis:hover {
            background: #0a5c83;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(10, 92, 131, 0.35);
        }
        .btn-mantis-outline {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: white;
            color: #2563eb;
            border: 1px solid #2563eb;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .dark .btn-mantis-outline {
            background: #1e293b;
            border-color: #60a5fa;
            color: #60a5fa;
        }
        .btn-mantis-outline:hover {
            background: #eff6ff;
            transform: translateY(-2px);
        }
        .dark .btn-mantis-outline:hover {
            background: #1e293b;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
        <!-- Mobile overlay backdrop -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="lg:hidden fixed inset-0 bg-black/40 z-40" x-transition></div>

        <!-- Sidebar -->
        <div class="fixed top-0 left-0 h-full w-72 z-50 transform transition-transform duration-300 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            @include('layouts.partials.admin-sidebar')
        </div>

        <!-- Sidebar spacer (prevents content from going under fixed sidebar) -->
        <div class="w-72 flex-shrink-0 hidden lg:block"></div>

        <!-- Main Content -->
        <main class="flex-1 min-w-0 overflow-y-auto h-screen">
            <!-- Top Navigation -->
            <header class="bg-[#0f6f9d] border-b border-[#0a5c83] sticky top-0 z-40 transition-colors duration-300">
                <div class="flex justify-between items-center px-4 md:px-8 py-4">
                    <div class="flex items-center min-w-0">
                        <button class="lg:hidden text-white/90 mr-3 flex-shrink-0" @click="sidebarOpen = !sidebarOpen">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="text-xl md:text-2xl font-bold text-white truncate transition-colors">@yield('header', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center space-x-2 md:space-x-4 flex-shrink-0">
                        <a href="{{ route('home') }}" target="_blank" class="hidden sm:inline text-white/90 hover:text-white transition-colors text-sm">
                            <i class="fas fa-external-link-alt mr-1"></i>View Site
                        </a>
                        <x-notification-bell />
                        <a href="{{ route('profile.edit') }}" class="flex items-center hover:opacity-80 transition-opacity" title="Edit Profile">
                            <img src="{{ asset('images/RGV-Logo.png') }}" alt="RGV Multi-Tech Services logo" class="w-9 h-9 md:w-10 md:h-10 rounded-xl object-cover shadow-lg shadow-blue-500/30 flex-shrink-0">
                            <div class="ml-2 md:ml-3 hidden md:block">
                                <p class="font-semibold text-white text-sm leading-tight transition-colors">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-white/70 transition-colors">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Employee' }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            @if(session('success'))
                <div class="mx-4 md:mx-8 mt-4 bg-gradient-to-r from-[#eff6ff] to-[#dbeafe] dark:from-blue-900/30 dark:to-blue-800/20 border border-[#2563eb] dark:border-blue-700 text-[#1d4ed8] dark:text-blue-300 px-4 py-3 rounded-xl transition-colors">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mx-4 md:mx-8 mt-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl transition-colors">
                    {{ session('error') }}
                </div>
            @endif

            @if(session()->has('impersonate.original_id'))
                <div class="mx-4 md:mx-8 mt-4 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 px-4 py-3 rounded-xl flex items-center justify-between transition-colors">
                    <span class="text-sm"><i class="fas fa-user-secret mr-2"></i>Impersonating <strong>{{ auth()->user()->name }}</strong></span>
                    <form action="{{ route('impersonation.stop') }}" method="POST" class="flex-shrink-0 ml-3">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-amber-600 text-white rounded-lg text-xs hover:bg-amber-700 whitespace-nowrap">
                            Stop
                        </button>
                    </form>
                </div>
            @endif

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
                        Free up space to prevent data loss.
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
        <script>
            document.addEventListener('alpine:init', () => {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: @json(session('success')), type: 'success' } }));
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('alpine:init', () => {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: @json(session('error')), type: 'error' } }));
            });
        </script>
    @endif

    @stack('scripts')

    <script>
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const chevron = document.getElementById(dropdownId.replace('-dropdown', '-chevron'));
            if (dropdown && chevron) {
                dropdown.classList.toggle('open');
                chevron.classList.toggle('rotate');
            }
        }
    </script>
</body>
</html>
