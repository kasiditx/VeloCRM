<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" style="background-color:#030712">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ velocrm_app_name() }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|prompt:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Dark mode & Transition Control (Rock solid for Livewire SPA) -->
        <script>
            // 1. Immediate theme application — runs before ANYTHING paints
            (function() {
                var theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                var d = document.documentElement;
                if (theme === 'dark') {
                    d.classList.add('dark');
                    d.style.backgroundColor = '#030712';
                    d.style.colorScheme = 'dark';
                } else {
                    d.classList.remove('dark');
                    d.style.backgroundColor = '#f9fafb';
                    d.style.colorScheme = 'light';
                }
                window.getTheme = function() {
                    return localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                };
            })();

            // 2. Global Toggle (Available everywhere)
            window.toggleTheme = function() {
                var current = getTheme();
                var next = current === 'dark' ? 'light' : 'dark';
                localStorage.setItem('theme', next);
                var d = document.documentElement;
                d.classList.toggle('dark', next === 'dark');
                d.style.backgroundColor = next === 'dark' ? '#030712' : '#f9fafb';
                d.style.colorScheme = next === 'dark' ? 'dark' : 'light';
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: next }));
            };

            // 3. Lock the dark class on HTML to prevent Livewire from removing it during DOM morphs
            (function() {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            var isDark = getTheme() === 'dark';
                            var d = document.documentElement;
                            if (isDark && !d.classList.contains('dark')) {
                                d.classList.add('dark');
                                d.style.backgroundColor = '#030712';
                            } else if (!isDark && d.classList.contains('dark')) {
                                d.classList.remove('dark');
                                d.style.backgroundColor = '#f9fafb';
                            }
                        }
                    });
                });
                observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            })();

            // 4. Suppress CSS transitions during navigation to prevent color jumping
            document.addEventListener('livewire:navigating', function () {
                document.documentElement.classList.add('livewire-navigating');
            });
            document.addEventListener('livewire:navigated', function () {
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        document.documentElement.classList.remove('livewire-navigating');
                        var page = document.querySelector('[data-page-motion]');
                        if (page && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                            page.classList.remove('page-arrived');
                            void page.offsetWidth;
                            page.classList.add('page-arrived');
                        }
                    });
                });
            });
            document.addEventListener('DOMContentLoaded', function () {
                var page = document.querySelector('[data-page-motion]');
                if (page && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    page.classList.add('page-arrived');
                }
            });
        </script>

        <!-- Dynamic Branding -->
        @php
            $primaryColor = \App\Models\Setting::get('primary_color', '#4f46e5');
            $favicon = \App\Models\Setting::get('favicon');
        @endphp
        <style>
            :root {
                --color-primary: {{ $primaryColor }};
                --color-primary-600: {{ $primaryColor }};
            }
            [x-cloak] { display: none !important; }
            /* Sidebar layout */
            body { display: flex; min-height: 100vh; }
            #sidebar { flex-shrink: 0; }
            #page-shell { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        </style>
        @if($favicon)
            <link rel="icon" type="image/png" href="{{ asset('uploads/' . $favicon) }}">
        @endif
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100"
        x-data="{
            shortcutsOpen: false,
            quickCreateOpen: false,
            focusIsTyping() {
                const tag = document.activeElement?.tagName?.toLowerCase()
                return ['input', 'textarea', 'select'].includes(tag) || document.activeElement?.isContentEditable
            },
            go(path) {
                window.location.href = path
            },
            contextCreate() {
                const path = window.location.pathname
                if (path.includes('/customers')) return this.go('{{ route('customers.create') }}')
                if (path.includes('/invoices')) return this.go('{{ route('invoices.create') }}')
                if (path.includes('/tasks')) return this.go('{{ route('tasks.create') }}')
                return this.go('{{ route('leads.create') }}')
            }
        }"
        x-on:keydown.window="
            if (focusIsTyping()) return;
            if ($event.key === '?') { $event.preventDefault(); shortcutsOpen = ! shortcutsOpen; return; }
            if ($event.key === 'n') { $event.preventDefault(); contextCreate(); return; }
        "
        x-on:keydown.g.window="
            if (focusIsTyping()) return;
            window.__veloWaitingForGo = true;
            setTimeout(() => window.__veloWaitingForGo = false, 900);
        "
        x-on:keydown.d.window="if (window.__veloWaitingForGo && ! focusIsTyping()) { $event.preventDefault(); go('{{ route('dashboard') }}'); }"
        x-on:keydown.l.window="if (window.__veloWaitingForGo && ! focusIsTyping()) { $event.preventDefault(); go('{{ route('leads.index') }}'); }"
        x-on:keydown.c.window="if (window.__veloWaitingForGo && ! focusIsTyping()) { $event.preventDefault(); go('{{ route('customers.index') }}'); }"
        x-on:keydown.i.window="if (window.__veloWaitingForGo && ! focusIsTyping()) { $event.preventDefault(); go('{{ route('invoices.index') }}'); }"
    >

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="delight-toast fixed top-4 right-4 z-[70] min-w-72 max-w-sm overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 shadow-lg dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-100">
                <div class="flex items-start gap-3 p-4">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900 dark:text-emerald-300">
                        <svg class="delight-toast-mark h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold">{{ __('Saved.') }}</p>
                        <p class="mt-0.5 text-sm text-emerald-700 dark:text-emerald-300">{{ session('success') }}</p>
                    </div>
                    <button @click="show = false" class="rounded-lg p-1 text-emerald-500 transition hover:bg-emerald-100 hover:text-emerald-700 dark:hover:bg-emerald-900"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="h-0.5 bg-emerald-200 dark:bg-emerald-900">
                    <div class="h-full bg-emerald-500" style="animation: velo-progress 1.4s cubic-bezier(0.22, 1, 0.36, 1) infinite;"></div>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="delight-toast fixed top-4 right-4 z-[70] min-w-72 max-w-sm rounded-xl border border-rose-200 bg-rose-50 text-rose-900 shadow-lg dark:border-rose-800 dark:bg-rose-950 dark:text-rose-100">
                <div class="flex items-start gap-3 p-4">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-900 dark:text-rose-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M12 8v4m0 4h.01"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold">{{ __('Needs attention') }}</p>
                        <p class="mt-0.5 text-sm text-rose-700 dark:text-rose-300">{{ session('error') }}</p>
                    </div>
                    <button @click="show = false" class="rounded-lg p-1 text-rose-500 transition hover:bg-rose-100 hover:text-rose-700 dark:hover:bg-rose-900"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
            </div>
        @endif

        <!-- Navigation sidebar + mobile drawer -->
        <livewire:layout.navigation />

        <!-- Page Content Shell -->
        <div id="page-shell">
            <!-- Desktop top bar inside shell -->
            <header class="hidden lg:flex items-center justify-end h-14 px-6 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 sticky top-0 z-30 gap-3"
                    x-data="{ currentTheme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') }"
                    @theme-changed.window="currentTheme = $event.detail">
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = ! open"
                        class="inline-flex h-9 items-center gap-2 rounded-xl bg-primary-600 px-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Quick Create') }}
                    </button>
                    <div x-show="open" x-cloak x-transition
                        class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl dark:border-gray-800 dark:bg-gray-900">
                        @foreach([
                            ['href' => route('leads.create'), 'label' => __('Lead')],
                            ['href' => route('customers.create'), 'label' => __('Customer')],
                            ['href' => route('invoices.create'), 'label' => __('Invoice')],
                            ['href' => route('tasks.create'), 'label' => __('Task')],
                        ] as $item)
                            <a href="{{ $item['href'] }}" wire:navigate @click="open = false"
                               class="flex items-center justify-between px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
                                <span>{{ $item['label'] }}</span>
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
                <button type="button" @click="shortcutsOpen = true"
                    class="inline-flex h-9 items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                    <span class="text-xs">?</span>
                    {{ __('Shortcuts') }}
                </button>
                <!-- Search -->
                <div class="w-56">
                    <livewire:global-search />
                </div>
                <!-- Dark toggle -->
                <button onclick="window.toggleTheme()"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg x-show="currentTheme === 'light'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3c0 .34.02.67.05 1A7 7 0 0020 12c.33.03.66.05 1 .05z"/></svg>
                    <svg x-show="currentTheme === 'dark'" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.485-8.485h-1M4.515 12.515h-1m11.314-5.657l-.707.707M7.172 16.828l-.707.707m9.192 0l-.707-.707M7.172 7.172l-.707-.707M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </button>
                <!-- Language -->
                <form method="POST" action="{{ route('locale.switch') }}">
                    @csrf
                    <select name="locale" onchange="this.form.submit()"
                        class="h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-transparent px-2 text-xs font-semibold text-gray-600 dark:text-gray-200 focus:ring-0 cursor-pointer">
                        <option value="en" @selected(app()->getLocale() === 'en')>EN</option>
                        <option value="th" @selected(app()->getLocale() === 'th')>TH</option>
                    </select>
                </form>
            </header>
            <!-- Page Content -->
            <main data-page-motion class="page-motion pt-14 lg:pt-0">
                {{ $slot }}
            </main>
        </div>

        <!-- Global Livewire Loading Bar -->
        <div wire:loading.delay.longer class="fixed left-0 top-0 z-[70] h-0.5 w-full overflow-hidden">
            <div class="h-full w-full origin-left bg-primary-600 dark:bg-primary-400" style="animation: velo-progress 1.1s cubic-bezier(0.22, 1, 0.36, 1) infinite;"></div>
        </div>

        <div
            x-data="{
                open: false,
                message: '',
                action: null,
                isDestructive() {
                    return /delete|trash|remove|deactivate|ลบ/i.test(this.message)
                },
                confirm() {
                    const callback = this.action
                    this.open = false
                    this.action = null

                    if (typeof callback === 'function') {
                        callback()
                    }
                },
                cancel() {
                    this.open = false
                    this.action = null
                },
            }"
            x-on:velo-confirm:open.window="
                message = $event.detail.message || '{{ __('Are you sure?') }}'
                action = $event.detail.onConfirm
                open = true
                $nextTick(() => $refs.cancelButton?.focus())
            "
            x-on:keydown.escape.window="open ? cancel() : null"
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[90] flex items-center justify-center px-4 py-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="global-confirm-title"
        >
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm"
                x-on:click="cancel()"
            ></div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl"
                            :class="isDestructive()
                                ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-300'
                                : 'bg-primary-50 text-primary-600 dark:bg-primary-950/60 dark:text-primary-300'"
                        >
                            <svg x-show="isDestructive()" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            </svg>
                            <svg x-show="! isDestructive()" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9.247a4 4 0 117.044 2.626c-.978.545-1.522 1.072-1.522 2.127M12 18h.01" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 id="global-confirm-title" class="text-base font-semibold text-gray-950 dark:text-gray-50">
                                {{ __('Confirm action') }}
                            </h2>
                            <p class="mt-1.5 text-sm leading-6 text-gray-600 dark:text-gray-300" x-text="message"></p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-800 dark:bg-gray-950/60 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        x-ref="cancelButton"
                        x-on:click="cancel()"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="button"
                        x-on:click="confirm()"
                        class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                        :class="isDestructive()
                            ? 'bg-rose-600 hover:bg-rose-700 focus-visible:ring-rose-500'
                            : 'bg-primary-600 hover:bg-primary-700 focus-visible:ring-primary-500'"
                    >
                        {{ __('Confirm') }}
                    </button>
                </div>
            </div>
        </div>

        <div x-show="shortcutsOpen" x-cloak x-on:keydown.escape.window="shortcutsOpen = false"
            class="fixed inset-0 z-[85] flex items-center justify-center px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="shortcuts-title">
            <div x-show="shortcutsOpen" x-transition.opacity class="absolute inset-0 bg-gray-950/55" @click="shortcutsOpen = false"></div>
            <div x-show="shortcutsOpen" x-transition
                class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                    <div>
                        <h2 id="shortcuts-title" class="text-base font-bold text-gray-950 dark:text-gray-50">{{ __('Keyboard shortcuts') }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Move through daily CRM work without leaving the keyboard.') }}</p>
                    </div>
                    <button type="button" @click="shortcutsOpen = false" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200" aria-label="{{ __('Close') }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="grid gap-2 p-6">
                    @foreach([
                        ['keys' => 'Ctrl/⌘ K', 'label' => __('Global search')],
                        ['keys' => 'g d', 'label' => __('Dashboard')],
                        ['keys' => 'g l', 'label' => __('Leads')],
                        ['keys' => 'g c', 'label' => __('Customers')],
                        ['keys' => 'g i', 'label' => __('Invoices')],
                        ['keys' => 'n', 'label' => __('New record for this page')],
                        ['keys' => '?', 'label' => __('Open this overlay')],
                    ] as $shortcut)
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $shortcut['label'] }}</span>
                            <kbd class="rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-bold text-gray-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300">{{ $shortcut['keys'] }}</kbd>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Plugins -->
        <script>
            (function () {
                function loadLivewireSortable() {
                    if (! window.Livewire || document.querySelector('script[data-livewire-sortable]')) {
                        return;
                    }

                    var script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/gh/livewire/sortable@v1.x.x/dist/livewire-sortable.js';
                    script.defer = true;
                    script.dataset.livewireSortable = 'true';
                    document.body.appendChild(script);
                }

                if (window.Livewire) {
                    loadLivewireSortable();
                } else {
                    document.addEventListener('livewire:init', loadLivewireSortable, { once: true });
                }
            })();

            (function () {
                function bindDraftForms() {
                    document.querySelectorAll('form[data-draft-key]:not([data-draft-bound])').forEach(function (form) {
                        var key = form.dataset.draftKey;
                        form.dataset.draftBound = 'true';

                        try {
                            var saved = JSON.parse(localStorage.getItem(key) || '{}');
                            Object.keys(saved).forEach(function (model) {
                                var field = form.querySelector('[wire\\:model="' + model + '"], [wire\\:model\\.live="' + model + '"], [wire\\:model\\.live\\.debounce\\.500ms="' + model + '"]');
                                if (field && field.value === '') {
                                    field.value = saved[model];
                                    field.dispatchEvent(new Event('input', { bubbles: true }));
                                    field.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                            });
                        } catch (error) {
                            localStorage.removeItem(key);
                        }

                        form.addEventListener('input', function (event) {
                            var target = event.target;
                            var model = target.getAttribute('wire:model') || target.getAttribute('wire:model.live') || target.getAttribute('wire:model.live.debounce.500ms');
                            if (! model || target.type === 'file') return;

                            var current = {};
                            try {
                                current = JSON.parse(localStorage.getItem(key) || '{}');
                            } catch (error) {
                                current = {};
                            }
                            current[model] = target.value;
                            localStorage.setItem(key, JSON.stringify(current));
                        });

                        form.addEventListener('submit', function () {
                            localStorage.removeItem(key);
                        });
                    });
                }

                document.addEventListener('DOMContentLoaded', bindDraftForms);
                document.addEventListener('livewire:navigated', bindDraftForms);
            })();
        </script>
    </body>
</html>
