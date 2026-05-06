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
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100">

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
        </script>
    </body>
</html>
