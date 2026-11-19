<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @if(session()->has('impersonate.original_id'))
                <div class="flex items-center justify-between gap-3 bg-amber-50 border-b border-amber-200 px-4 py-3 text-amber-800">
                    <span class="text-sm"><i class="fas fa-user-secret mr-2"></i>Impersonating <strong>{{ auth()->user()->name }}</strong></span>
                    <form action="{{ route('impersonation.stop') }}" method="POST" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-amber-600 text-white rounded-lg text-xs hover:bg-amber-700 whitespace-nowrap">Stop Impersonating</button>
                    </form>
                </div>
            @endif
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        <x-toast-container />
    </body>
</html>
