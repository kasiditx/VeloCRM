<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold leading-tight text-gray-900 dark:text-white">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    @php
        $user = auth()->user();
        $initials = collect(preg_split('/\s+/', trim($user->name ?? '')))
            ->filter()
            ->map(fn ($p) => mb_substr($p, 0, 1))
            ->take(2)
            ->implode('');
        $initials = $initials !== '' ? mb_strtoupper($initials) : mb_strtoupper(mb_substr($user->email ?? '?', 0, 1));
    @endphp

    <div
        class="module-page"
        x-data="{ tab: (window.location.hash || '').replace('#','') || 'profile' }"
        x-on:hashchange.window="tab = (window.location.hash || '').replace('#','') || 'profile'"
    >
        <div class="module-container">

            <div class="module-header">
                <div>
                    <h1 class="module-title">{{ __('Account') }}</h1>
                    <p class="module-subtitle">
                        {{ __('Manage your personal information, sign-in credentials, and account lifecycle.') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-6">

                {{-- Left: identity summary + section tabs --}}
                <aside class="md:w-64 shrink-0 space-y-4">
                    <div class="module-panel p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-50 text-sm font-semibold text-primary-700 ring-1 ring-inset ring-primary-100 dark:bg-primary-900/40 dark:text-primary-200 dark:ring-primary-900/60">
                                {{ $initials }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </div>

                    <nav class="module-panel p-2 space-y-1" aria-label="{{ __('Account sections') }}">
                        @php
                            $tabs = [
                                ['key' => 'profile',  'label' => __('Profile'),  'hint' => __('Name and email'), 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                                ['key' => 'password', 'label' => __('Password'), 'hint' => __('Sign-in credentials'), 'icon' => 'M12 11c1.657 0 3-1.343 3-3V6a3 3 0 10-6 0v2c0 1.657 1.343 3 3 3zM5 11h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z'],
                                ['key' => 'danger',   'label' => __('Danger zone'), 'hint' => __('Delete account'), 'icon' => 'M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z'],
                            ];
                        @endphp

                        @foreach($tabs as $t)
                            <a
                                href="#{{ $t['key'] }}"
                                x-on:click="tab = '{{ $t['key'] }}'"
                                :class="tab === '{{ $t['key'] }}'
                                    ? 'bg-primary-600 text-white shadow-sm'
                                    : '{{ $t['key'] === 'danger' ? 'text-rose-600 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}'"
                                class="group flex items-start gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
                            >
                                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}" />
                                </svg>
                                <span class="min-w-0">
                                    <span class="block leading-5">{{ $t['label'] }}</span>
                                    <span
                                        :class="tab === '{{ $t['key'] }}' ? 'text-primary-100' : 'text-gray-500 dark:text-gray-400'"
                                        class="block text-xs font-normal leading-4"
                                    >{{ $t['hint'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </nav>
                </aside>

                {{-- Right: section panels --}}
                <div class="flex-1 min-w-0">
                    <section x-show="tab === 'profile'" x-cloak class="module-panel p-6 sm:p-8">
                        <livewire:profile.update-profile-information-form />
                    </section>

                    <section x-show="tab === 'password'" x-cloak class="module-panel p-6 sm:p-8">
                        <livewire:profile.update-password-form />
                    </section>

                    <section x-show="tab === 'danger'" x-cloak class="module-panel border-rose-200 dark:border-rose-900/60 p-6 sm:p-8">
                        <livewire:profile.delete-user-form />
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
