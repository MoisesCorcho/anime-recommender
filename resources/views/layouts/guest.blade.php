<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Anime Recommender') }} — {{ $title ?? 'Bienvenido' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&family=manrope:400,500,600&display=swap" rel="stylesheet" />

        <!-- Material Symbols -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        {{-- Animated background --}}
        <div class="fixed inset-0 auth-bg -z-10"></div>

        {{-- Decorative orbs --}}
        <div class="fixed top-[-15%] left-[-10%] w-[55vw] h-[55vw] auth-orb-primary -z-10 animate-pulse" style="animation-duration: 6s;"></div>
        <div class="fixed bottom-[-20%] right-[-15%] w-[50vw] h-[50vw] auth-orb-secondary -z-10 animate-pulse" style="animation-duration: 8s; animation-delay: 2s;"></div>

        <div class="min-h-screen flex flex-col items-center justify-center px-4 sm:px-6 py-12">

            {{-- Logo / Brand --}}
            <div class="mb-8 flex flex-col items-center gap-3">
                <a href="/" wire:navigate class="group flex flex-col items-center gap-2">
                    <x-application-logo class="w-14 h-14 fill-current text-[var(--color-primary)] transition-opacity duration-200 group-hover:opacity-80" />
                    <span class="font-headline text-sm font-semibold tracking-[0.2em] uppercase text-[var(--color-on-surface-variant)]">
                        Anime Recommender
                    </span>
                </a>
            </div>

            {{-- Auth Card --}}
            <div class="w-full max-w-md auth-card rounded-2xl p-8 sm:p-10">
                {{ $slot }}
            </div>

        </div>
    </body>
</html>
