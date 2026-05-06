<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ velocrm_app_name() }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900|prompt:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .auth-bg-pattern {
                background-color: #111827;
                background-image:
                    radial-gradient(at 24% 16%, rgba(79, 70, 229, 0.42) 0px, transparent 44%),
                    radial-gradient(at 84% 12%, rgba(2, 132, 199, 0.24) 0px, transparent 38%),
                    linear-gradient(145deg, #111827 0%, #0f172a 52%, #030712 100%);
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-12px); }
            }
            .float-1 { animation: float 4s ease-in-out infinite; }
            .float-2 { animation: float 5s ease-in-out infinite 0.8s; }
            .float-3 { animation: float 6s ease-in-out infinite 1.6s; }
        </style>
    </head>
    <body class="antialiased min-h-screen">
        <div class="flex min-h-screen">

            {{-- ── LEFT PANEL: Branding ── --}}
            <div class="auth-bg-pattern hidden lg:flex lg:w-1/2 xl:w-[55%] flex-col p-12 relative overflow-hidden">

                {{-- Quiet ambient shapes --}}
                <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-primary-500/10 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 rounded-full bg-sky-500/10 blur-3xl"></div>

                {{-- Logo --}}
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-black text-white tracking-tight">{{ velocrm_app_name() }}</span>
                </div>

                {{-- Main illustration area --}}
                <div class="flex-1 flex flex-col items-center justify-center relative z-10 py-12">
                    {{-- Floating card mockups --}}
                    <div class="relative w-full max-w-md">

                        {{-- Main dashboard card --}}
                        <div class="float-1 rounded-2xl border border-white/10 bg-white/[0.08] p-6 shadow-xl">
                            <div class="flex items-center justify-between mb-5">
                                <div>
                                    <p class="text-white/60 text-xs font-medium uppercase tracking-widest">{{ __('Workspace') }}</p>
                                    <p class="text-white text-3xl font-bold mt-0.5">{{ __('Ready') }}</p>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-green-400/20 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </div>
                            </div>
                            {{-- Mini Bar Chart --}}
                            <div class="flex items-end gap-1.5 h-16">
                                @foreach([40, 65, 45, 80, 60, 90, 75, 95, 70, 100, 85, 92] as $h)
                                    <div class="flex-1 bg-white/20 rounded-full" style="height: {{ $h }}%"></div>
                                @endforeach
                            </div>
                            <div class="flex items-center justify-between mt-4">
                                <span class="text-white/50 text-xs">{{ __('This month') }}</span>
                                <span class="flex items-center gap-1 text-green-300 text-xs font-semibold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                    +24.8%
                                </span>
                            </div>
                        </div>

                        {{-- Floating lead card --}}
                        <div class="float-2 absolute -right-8 top-16 rounded-2xl border border-gray-100 bg-white/95 p-4 shadow-xl w-48">
                            <div class="flex items-center gap-2.5 mb-3">
                                <div class="w-8 h-8 rounded-xl bg-violet-100 flex items-center justify-center text-violet-600 font-bold text-sm">S</div>
                                <div>
                                    <p class="text-gray-900 text-xs font-semibold leading-none">{{ __('Lead') }}</p>
                                    <p class="text-gray-400 text-[10px] mt-0.5">{{ __('New Lead') }}</p>
                                </div>
                            </div>
                            <div class="text-primary-600 text-sm font-bold">{{ __('Follow up') }}</div>
                            <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-600 rounded-full" style="width: 65%"></div>
                            </div>
                        </div>

                        {{-- Floating stats badge --}}
                        <div class="float-3 absolute -left-8 bottom-4 bg-white/95 rounded-2xl p-4 shadow-xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-[10px] font-medium">{{ __('Next action') }}</p>
                                    <p class="text-gray-900 text-xl font-bold leading-none mt-0.5">{{ __('Clear') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tagline --}}
                <div class="relative z-10">
                    <h2 class="text-3xl xl:text-4xl font-extrabold text-white leading-tight">
                        {{ velocrm_company_name() }}<br>
                        <span class="text-white/60">{{ __('ready for the next action.') }}</span>
                    </h2>
                    <p class="text-white/50 text-sm mt-3 max-w-xs">{{ velocrm_auth_subtitle() }}</p>
                </div>
            </div>

            {{-- ── RIGHT PANEL: Login Form ── --}}
            <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 sm:px-12 bg-gray-50 dark:bg-gray-950">

                {{-- Mobile logo --}}
                <div class="lg:hidden flex items-center gap-2 mb-10 self-start w-full max-w-md">
                    <div class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-black text-gray-900 dark:text-white tracking-tight">{{ velocrm_app_name() }}</span>
                </div>

                <div class="w-full max-w-md">
                    <div class="mb-8">
                        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ velocrm_auth_headline() }}</h1>
                        <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">{{ velocrm_auth_subtitle() }}</p>
                    </div>

                    {{ $slot }}

                    <p class="mt-8 text-center text-xs text-gray-400 dark:text-gray-600">
                        © {{ date('Y') }} {{ velocrm_app_name() }}. {{ __('All rights reserved.') }}
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
