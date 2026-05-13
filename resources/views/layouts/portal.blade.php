<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" style="background-color:#f8fafc">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ velocrm_app_name() }} Portal</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|prompt:300,400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full bg-slate-50 font-sans text-slate-950 antialiased dark:bg-slate-950 dark:text-slate-50">
        <div class="min-h-screen">
            <header class="border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
                <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div>
                        <a href="{{ route('portal.invoices.index') }}" wire:navigate class="text-lg font-black tracking-tight text-primary-700 dark:text-primary-300">{{ velocrm_app_name() }}</a>
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">{{ __('Customer Portal') }}</p>
                    </div>
                    <nav class="flex flex-wrap items-center gap-2 text-sm font-semibold">
                        <a href="{{ route('portal.invoices.index') }}" wire:navigate class="rounded-xl px-3 py-2 transition {{ request()->routeIs('portal.invoices.*') ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">{{ __('Invoices') }}</a>
                        <a href="{{ route('portal.profile') }}" wire:navigate class="rounded-xl px-3 py-2 transition {{ request()->routeIs('portal.profile') ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">{{ __('Profile') }}</a>
                    </nav>
                </div>
            </header>

            @if (session()->has('success'))
                <div class="mx-auto mt-4 max-w-6xl px-4 sm:px-6 lg:px-8">
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">{{ session('success') }}</div>
                </div>
            @endif

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
