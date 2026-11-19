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
        <style>
            :root {
                --primary: #2563eb;
                --primary-dark: #1e40af;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-gray-50 to-[#eff6ff]">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">
            @if(session()->has('impersonate.original_id') && auth()->check())
                <div class="mb-6 flex w-full max-w-[440px] items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
                    <span class="text-sm"><i class="fas fa-user-secret mr-2"></i>Impersonating <strong>{{ auth()->user()->name }}</strong></span>
                    <form action="{{ route('impersonation.stop') }}" method="POST" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-amber-600 text-white rounded-lg text-xs hover:bg-amber-700 whitespace-nowrap">Stop Impersonating</button>
                    </form>
                </div>
            @endif
            <div class="mb-10">
                <a href="/">
                    <img src="{{ asset('images/RGV-Logo.png') }}" alt="RGV Multi-Tech Services logo" class="h-[160px] w-[160px] rounded-full object-cover shadow-lg shadow-[#2563eb]/20">
                </a>
            </div>

            <div class="w-full max-w-[440px] rounded-xl border border-gray-100 bg-white px-6 py-6 shadow-xl">
                {{ $slot }}
            </div>
        </div>
        <x-toast-container />
    </body>
</html>
