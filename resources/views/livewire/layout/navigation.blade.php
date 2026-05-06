<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

{{-- ═══════════════════════════════════════════════════════════════
     SIDEBAR NAVIGATION — VeloCRM Premium SaaS
     Renders as: <aside id="sidebar"> inside body flex row
     Page shell (#page-shell) contains topbar + main content
     ═══════════════════════════════════════════════════════════════ --}}
<div
    id="sidebar"
    x-data="{
        collapsed: localStorage.getItem('sidebar_collapsed') === 'true',
        mobileOpen: false,
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebar_collapsed', this.collapsed);
            // Sync page-shell margin
            const shell = document.getElementById('page-shell');
            if (shell) {
                shell.style.marginLeft = '';
            }
        }
    }"
>
    {{-- ─── DESKTOP SIDEBAR ─── --}}
    <aside
        :class="collapsed ? 'w-16' : 'w-60'"
        class="hidden lg:flex flex-col bg-white dark:bg-gray-900 border-r border-gray-100 dark:border-gray-800 transition-[width] duration-200 ease-in-out sticky top-0 h-screen z-40 overflow-hidden shrink-0"
    >
        {{-- Logo --}}
        <div class="flex items-center h-14 px-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2.5 min-w-0">
                <div class="w-7 h-7 rounded-lg bg-primary-600 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span x-show="!collapsed" x-cloak class="text-[15px] font-black text-gray-900 dark:text-white tracking-tight">
                    Velo<span class="text-primary-600">CRM</span>
                </span>
            </a>
        </div>

        {{-- Search in sidebar (expanded only) --}}
        <div x-show="!collapsed" x-cloak class="px-3 pt-3 pb-1 shrink-0">
            <livewire:global-search />
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-2 px-2 space-y-0.5 overflow-x-hidden">

            @php
            $navItems = [
                ['route' => 'dashboard', 'label' => __('Dashboard'), 'match' => 'dashboard',
                 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'leads.index', 'label' => __('Leads'), 'match' => 'leads.*',
                 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['route' => 'customers.index', 'label' => __('Customers'), 'match' => 'customers.*',
                 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['route' => 'invoices.index', 'label' => __('Invoices'), 'match' => 'invoices.*',
                 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['route' => 'proposals.index', 'label' => __('Proposals'), 'match' => 'proposals.*',
                 'icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
            ];
            $toolItems = [
                ['route' => 'tasks.index', 'label' => __('Tasks'), 'match' => 'tasks.*',
                 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7l2 2 4-4'],
                ['route' => 'calendar.index', 'label' => __('Calendar'), 'match' => 'calendar.*',
                 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['route' => 'leads.kanban', 'label' => __('Pipeline'), 'match' => 'leads.kanban',
                 'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2'],
            ];
            $adminItems = [
                ['route' => 'reports.index', 'label' => __('Reports'), 'match' => 'reports.*',
                 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ['route' => 'admin.users.index', 'label' => __('Users'), 'match' => 'admin.users.*',
                 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['route' => 'admin.settings', 'label' => __('Settings'), 'match' => 'admin.settings',
                 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ];
            @endphp

            {{-- Section label: Main --}}
            <div x-show="!collapsed" x-cloak class="px-2 pt-2 pb-1">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-600">{{ __('Main') }}</p>
            </div>

            @foreach($navItems as $item)
                @php $isActive = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}" wire:navigate
                   title="{{ $item['label'] }}"
                   class="relative flex items-center gap-3 px-2.5 py-2 rounded-lg text-sm font-medium transition-all group
                          {{ $isActive
                              ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300'
                              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                    @if($isActive)
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-primary-500 rounded-r-full"></span>
                    @endif
                    <svg class="w-5 h-5 shrink-0 transition-colors {{ $isActive ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}"/>
                    </svg>
                    <span x-show="!collapsed" x-cloak class="truncate leading-none">{{ $item['label'] }}</span>
                </a>
            @endforeach

            {{-- Section label: Tools --}}
            <div x-show="!collapsed" x-cloak class="px-2 pt-5 pb-1">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-600">{{ __('Tools') }}</p>
            </div>
            <div x-show="collapsed" x-cloak class="my-2 mx-1 border-t border-gray-100 dark:border-gray-800"></div>

            @foreach($toolItems as $item)
                @php $isActive = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}" wire:navigate
                   title="{{ $item['label'] }}"
                   class="relative flex items-center gap-3 px-2.5 py-2 rounded-lg text-sm font-medium transition-all group
                          {{ $isActive
                              ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300'
                              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                    @if($isActive)
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-primary-500 rounded-r-full"></span>
                    @endif
                    <svg class="w-5 h-5 shrink-0 transition-colors {{ $isActive ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}"/>
                    </svg>
                    <span x-show="!collapsed" x-cloak class="truncate leading-none">{{ $item['label'] }}</span>
                </a>
            @endforeach

            {{-- Admin Section --}}
            @role('Admin')
            <div x-show="!collapsed" x-cloak class="px-2 pt-5 pb-1">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-600">{{ __('Admin') }}</p>
            </div>
            <div x-show="collapsed" x-cloak class="my-2 mx-1 border-t border-gray-100 dark:border-gray-800"></div>

            @foreach($adminItems as $item)
                @php $isActive = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}" wire:navigate
                   title="{{ $item['label'] }}"
                   class="relative flex items-center gap-3 px-2.5 py-2 rounded-lg text-sm font-medium transition-all group
                          {{ $isActive
                              ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300'
                              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                    @if($isActive)
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-primary-500 rounded-r-full"></span>
                    @endif
                    <svg class="w-5 h-5 shrink-0 transition-colors {{ $isActive ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}"/>
                    </svg>
                    <span x-show="!collapsed" x-cloak class="truncate leading-none">{{ $item['label'] }}</span>
                </a>
            @endforeach
            @endrole

        </nav>

        {{-- User + Collapse footer --}}
        <div class="shrink-0 border-t border-gray-100 dark:border-gray-800">
            <div x-show="!collapsed" x-cloak class="px-3 pt-3">
                <div class="rounded-xl border border-primary-100 bg-primary-50/70 p-3 text-primary-900 dark:border-primary-900/50 dark:bg-primary-950/40 dark:text-primary-100">
                    <div class="flex items-start gap-2">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-white text-primary-600 shadow-sm dark:bg-primary-900 dark:text-primary-300">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold">{{ __('Desk tip') }}</p>
                            <p class="mt-0.5 text-xs leading-5 text-primary-700 dark:text-primary-300">{{ __('Press Ctrl/⌘ K to jump to a lead or customer without leaving the page.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- User button --}}
            <div x-data="{ uOpen: false }" @click.outside="uOpen = false" class="relative p-2">
                <button @click="uOpen = !uOpen"
                    :title="collapsed ? '{{ auth()->user()->name }}' : ''"
                    class="w-full flex items-center gap-2.5 px-2 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-left group">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-primary-600 to-violet-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div x-show="!collapsed" x-cloak class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <svg x-show="!collapsed" x-cloak class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                    </svg>
                </button>

                {{-- User menu popup --}}
                <div x-show="uOpen" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                     class="absolute bottom-full left-2 right-2 mb-1 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 py-1 z-50 origin-bottom">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <p class="text-xs text-gray-400">{{ __('Signed in as') }}</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                        <span class="inline-block mt-2 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full {{ auth()->user()->hasRole('Admin') ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                        </span>
                    </div>
                    <a href="{{ route('profile') }}" wire:navigate
                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ __('Profile') }}
                    </a>
                    <button wire:click="logout"
                       class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors border-t border-gray-100 dark:border-gray-700 mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        {{ __('Log Out') }}
                    </button>
                </div>
            </div>

            {{-- Collapse toggle --}}
            <div class="px-3 pb-3">
                <button @click="toggleCollapse()"
                    class="w-full flex items-center justify-center gap-2 py-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-xs font-medium">
                    <svg class="w-4 h-4 transition-transform duration-200" :class="collapsed ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                    <span x-show="!collapsed" x-cloak>{{ __('Collapse') }}</span>
                </button>
            </div>
        </div>
    </aside>

    {{-- ─── TOP BAR (always visible, sits in #page-shell) ─── --}}
    {{-- Rendered separately in app.blade.php via a second livewire tag? No — we output it as part of this component --}}
    {{-- But since body is flex, this nav component IS the sidebar only.
         We need the topbar to be part of #page-shell. We'll use a different approach: --}}
    {{-- Output a hidden div that injects a topbar into the page-shell via JS — too complex.
         Better: put topbar here too, inside an absolutely-positioned element that page-shell adjusts to. --}}

    {{-- ─── MOBILE TOPBAR + DRAWER ─── --}}
    {{-- This is rendered at the top of the body (before page-shell) --}}
    <div class="lg:hidden fixed top-0 left-0 right-0 z-40 h-14 bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border-b border-gray-100 dark:border-gray-800 flex items-center px-4 gap-3"
         x-data="{ currentTheme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') }"
         @theme-changed.window="currentTheme = $event.detail">

        {{-- Brand --}}
        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-primary-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="font-black text-gray-900 dark:text-white tracking-tight">Velo<span class="text-primary-600">CRM</span></span>
        </a>

        <div class="flex items-center gap-2 ml-auto">
            {{-- Dark mode --}}
            <button onclick="window.toggleTheme()"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <svg x-show="currentTheme === 'light'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3c0 .34.02.67.05 1A7 7 0 0020 12c.33.03.66.05 1 .05z"/></svg>
                <svg x-show="currentTheme === 'dark'" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.485-8.485h-1M4.515 12.515h-1m11.314-5.657l-.707.707M7.172 16.828l-.707.707m9.192 0l-.707-.707M7.172 7.172l-.707-.707M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </button>

            {{-- Language --}}
            <form method="POST" action="{{ route('locale.switch') }}">
                @csrf
                <select name="locale" onchange="this.form.submit()"
                    class="h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2 text-xs font-semibold text-gray-600 dark:text-gray-200 focus:ring-0 cursor-pointer">
                    <option value="en" @selected(app()->getLocale() === 'en')>EN</option>
                    <option value="th" @selected(app()->getLocale() === 'th')>TH</option>
                </select>
            </form>

            {{-- Hamburger --}}
            <button @click="mobileOpen = !mobileOpen"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'hidden': mobileOpen}" class="inline" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path :class="{'hidden': !mobileOpen}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ─── MOBILE DRAWER ─── --}}
    <div x-show="mobileOpen"
         @click.self="mobileOpen = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-cloak
         class="lg:hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm">
        <div x-show="mobileOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="absolute inset-y-0 left-0 w-72 bg-white dark:bg-gray-900 shadow-2xl flex flex-col">

            <div class="flex items-center justify-between h-14 px-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
                <span class="font-black text-gray-900 dark:text-white">Velo<span class="text-primary-600">CRM</span></span>
                <button @click="mobileOpen = false" class="w-7 h-7 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-3 pt-3 pb-1 shrink-0">
                <livewire:global-search />
            </div>

            <nav class="flex-1 overflow-y-auto px-2 py-2 space-y-0.5">
                <div class="px-2 pt-2 pb-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">{{ __('Main') }}</p>
                </div>
                @foreach($navItems as $item)
                    @php $isActive = request()->routeIs($item['match']); @endphp
                    <a href="{{ route($item['route']) }}" wire:navigate @click="mobileOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                              {{ $isActive ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        <svg class="w-5 h-5 shrink-0 {{ $isActive ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <div class="px-2 pt-4 pb-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">{{ __('Tools') }}</p>
                </div>
                @foreach($toolItems as $item)
                    @php $isActive = request()->routeIs($item['match']); @endphp
                    <a href="{{ route($item['route']) }}" wire:navigate @click="mobileOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                              {{ $isActive ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        <svg class="w-5 h-5 shrink-0 {{ $isActive ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
                @role('Admin')
                <div class="px-2 pt-4 pb-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">{{ __('Admin') }}</p>
                </div>
                @foreach($adminItems as $item)
                    @php $isActive = request()->routeIs($item['match']); @endphp
                    <a href="{{ route($item['route']) }}" wire:navigate @click="mobileOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                              {{ $isActive ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        <svg class="w-5 h-5 shrink-0 {{ $isActive ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
                @endrole
            </nav>

            <div class="border-t border-gray-100 dark:border-gray-800 p-3 shrink-0">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-primary-600 to-violet-500 flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('profile') }}" wire:navigate @click="mobileOpen = false"
                       class="flex items-center justify-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ __('Profile') }}
                    </a>
                    <button wire:click="logout"
                       class="flex items-center justify-center gap-2 rounded-lg border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-900/20 px-3 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                        {{ __('Log Out') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end #sidebar --}}
