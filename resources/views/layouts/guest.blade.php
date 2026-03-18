<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PrintService') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-100 antialiased min-h-screen bg-slate-950">
        <div class="relative min-h-screen overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_14%_10%,rgba(56,189,248,0.18),transparent_34%),radial-gradient(circle_at_90%_0%,rgba(16,185,129,0.16),transparent_36%),linear-gradient(180deg,#020617,#050d1f_48%,#030712)]"></div>

            <div class="relative min-h-screen flex flex-col">
                <header class="max-w-6xl w-full mx-auto px-6 py-6 flex items-center justify-between">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <x-application-logo class="h-9 w-9" />
                        <div>
                            <div class="font-semibold tracking-wide">PrintService</div>
                            <div class="text-xs text-slate-400">Кабінет клієнта</div>
                        </div>
                    </a>
                </header>

                <main class="flex-1 flex items-center justify-center px-4 py-8">
                    <div @class([
                        'w-full px-6 py-5 bg-slate-900/80 border border-slate-800 shadow-2xl shadow-cyan-950/20 overflow-hidden rounded-xl backdrop-blur',
                        'sm:max-w-5xl' => request()->routeIs('login') || request()->routeIs('register'),
                        'sm:max-w-md' => ! (request()->routeIs('login') || request()->routeIs('register')),
                    ])>
                        {{ $slot }}
                    </div>
                </main>

                @include('layouts.partials.site-footer')
            </div>
        </div>
    </body>
</html>
