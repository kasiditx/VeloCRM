<div class="py-6 lg:py-8">
    <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('User Management') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create admins or staff accounts, control access, and disable users without deleting their data.') }}</p>
            </div>
            <x-button.primary-link href="{{ route('admin.users.create') }}" wire:navigate>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('New User') }}
            </x-button.primary-link>
        </div>

        {{-- Card Panel --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 dark:border-gray-800 p-5">
                <div class="relative max-w-md">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Name or email') }}"
                        class="block w-full rounded-xl border-gray-300 pl-9 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950/50 dark:text-gray-100">
                </div>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-950">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('User') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Role') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Created') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
                        @forelse ($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-primary-600 to-purple-500 flex items-center justify-center text-white text-sm font-bold shrink-0 shadow-sm">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($user->roles as $role)
                                            <x-ui.status-chip :status="$role->name">{{ $role->name }}</x-ui.status-chip>
                                        @empty
                                            <span class="text-sm text-gray-500 dark:text-gray-400 italic">{{ __('No role assigned') }}</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <x-ui.status-chip :status="$user->is_active ? 'active' : 'danger'">
                                        {{ $user->is_active ? __('Active') : __('Inactive') }}
                                    </x-ui.status-chip>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ format_date($user->created_at) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" wire:navigate title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"
                                            class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <button wire:click="toggleActive({{ $user->id }})" wire:loading.attr="disabled" wire:target="toggleActive({{ $user->id }})" title="{{ $user->is_active ? __('Disable') : __('Enable') }}" aria-label="{{ $user->is_active ? __('Disable') : __('Enable') }}"
                                            class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-primary-600 hover:bg-primary-50 disabled:pointer-events-none disabled:opacity-45 dark:hover:bg-primary-900/30 transition-colors">
                                            @if($user->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </button>
                                        <button wire:click="delete({{ $user->id }})" wire:loading.attr="disabled" wire:target="delete({{ $user->id }})" wire:confirm="{{ __('Delete this user?') }}" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"
                                            class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-rose-600 hover:bg-rose-50 disabled:pointer-events-none disabled:opacity-45 dark:hover:bg-rose-900/30 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <x-ui.empty-state
                                        icon="user"
                                        :title="__('No users found.')"
                                        :message="__('Create an admin or staff account to start assigning CRM work.')"
                                    >
                                        <x-button.primary-link href="{{ route('admin.users.create') }}" wire:navigate>{{ __('Add First User') }}</x-button.primary-link>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($users as $user)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary-600 to-purple-500 flex items-center justify-center text-white font-bold shrink-0 shadow-sm">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                            </div>
                            <x-ui.status-chip :status="$user->is_active ? 'active' : 'danger'" class="ml-auto shrink-0">
                                {{ $user->is_active ? __('Active') : __('Inactive') }}
                            </x-ui.status-chip>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Role') }}: {{ $user->roles->pluck('name')->join(', ') ?: __('No role assigned') }}
                            &nbsp;·&nbsp;
                            {{ __('Created') }}: {{ format_date($user->created_at) }}
                        </div>
                        <div class="flex gap-3 pt-1">
                            <a href="{{ route('admin.users.edit', $user->id) }}" wire:navigate class="text-xs font-medium text-amber-600 dark:text-amber-400">{{ __('Edit') }}</a>
                            <button wire:click="toggleActive({{ $user->id }})" wire:loading.attr="disabled" wire:target="toggleActive({{ $user->id }})" class="text-xs font-medium text-primary-600 disabled:pointer-events-none disabled:opacity-50 dark:text-primary-400">
                                <x-ui.loading-label target="toggleActive({{ $user->id }})" :label="$user->is_active ? __('Disable') : __('Enable')" :loading="__('Updating...')" />
                            </button>
                            <button wire:click="delete({{ $user->id }})" wire:loading.attr="disabled" wire:target="delete({{ $user->id }})" wire:confirm="{{ __('Delete this user?') }}" class="text-xs font-medium text-rose-600 disabled:pointer-events-none disabled:opacity-50 dark:text-rose-400">
                                <x-ui.loading-label target="delete({{ $user->id }})" :label="__('Delete')" :loading="__('Deleting...')" />
                            </button>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state
                        icon="user"
                        :title="__('No users found.')"
                        :message="__('Create an admin or staff account to start assigning CRM work.')"
                        size="compact"
                    />
                @endforelse
            </div>

            <div class="border-t border-gray-200 dark:border-gray-800 px-5 py-4 bg-gray-50/30 dark:bg-gray-900/30 rounded-b-2xl">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
