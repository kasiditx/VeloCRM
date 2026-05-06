<div class="module-page">
    <div class="module-container">

        {{-- Page Header --}}
        <div class="module-header">
            <div>
                <h1 class="module-title">{{ __('Leads') }}</h1>
                <p class="module-subtitle">
                    {{ $showTrashed ? __('Review deleted leads and restore records when needed.') : __('Track pipeline progress and convert qualified deals into customers.') }}
                </p>
            </div>
            <div class="module-actions">
                @if ($showTrashed)
                    <button wire:click="$toggle('showTrashed')" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-300 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 shadow-sm transition hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('Back to Active Leads') }}
                    </button>
                @else
                    <x-button.secondary wire:click="$toggle('showTrashed')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('Open Trash') }}
                    </x-button.secondary>
                @endif
                @unless ($showTrashed)
                    @role('Admin')
                    <x-button.secondary-link href="{{ route('leads.import') }}" wire:navigate>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        {{ __('Import Leads') }}
                    </x-button.secondary-link>
                    @endrole
                    <x-button.secondary-link href="{{ route('leads.kanban') }}" wire:navigate>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
                        {{ __('Kanban View') }}
                    </x-button.secondary-link>
                    <x-button.primary-link href="{{ route('leads.create') }}" wire:navigate>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('New Lead') }}
                    </x-button.primary-link>
                @endunless
            </div>
        </div>

        {{-- Filter + Table Card --}}
        <div class="module-panel">
            {{-- Filter Panel --}}
            <div class="module-filter grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Search') }}</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Name, email, phone, company') }}"
                            class="w-full rounded-xl border-gray-300 pl-9 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Status') }}</label>
                    <select wire:model.live="statusFilter" class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ __($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Source') }}</label>
                    <select wire:model.live="sourceFilter" class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        <option value="">{{ __('All sources') }}</option>
                        @foreach ($sources as $source)
                            <option value="{{ $source }}">{{ __($source) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="module-table">
                    <thead class="module-table-head">
                        <tr>
                            <th class="module-table-heading">
                                <button wire:click="sortBy('name')" class="inline-flex items-center gap-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ __('Lead') }}</button>
                            </th>
                            <th class="module-table-heading">{{ __('Contact') }}</th>
                            <th class="module-table-heading">
                                <button wire:click="sortBy('status')" class="inline-flex items-center gap-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ __('Status') }}</button>
                            </th>
                            <th class="module-table-heading">{{ __('Source') }}</th>
                            <th class="module-table-heading-right">
                                <button wire:click="sortBy('value')" class="inline-flex items-center gap-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ __('Value') }}</button>
                            </th>
                            <th class="module-table-heading-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="module-table-body">
                        @forelse ($leads as $lead)
                            <tr class="module-table-row group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-100 to-purple-100 dark:from-primary-900 dark:to-purple-900 flex items-center justify-center shrink-0">
                                            <span class="text-sm font-bold text-primary-600 dark:text-primary-400">{{ strtoupper(substr($lead->name, 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $lead->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $lead->company ?: __('No company') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <div>{{ $lead->email ?: __('No email') }}</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs mt-0.5">{{ $lead->phone ?: __('No phone') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <x-ui.status-chip :status="$lead->status">{{ __($lead->status) }}</x-ui.status-chip>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $lead->source ? __($lead->source) : __('Unspecified') }}</td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ format_currency($lead->value) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        @if ($showTrashed)
                                            <button wire:click="restore({{ $lead->id }})" wire:loading.attr="disabled" wire:target="restore({{ $lead->id }})" title="{{ __('Restore') }}" aria-label="{{ __('Restore') }}"
                                                class="module-icon-button hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </button>
                                            <button wire:click="forceDelete({{ $lead->id }})" wire:loading.attr="disabled" wire:target="forceDelete({{ $lead->id }})" wire:confirm="{{ __('Permanently delete this lead?') }}" title="{{ __('Delete Permanently') }}" aria-label="{{ __('Delete Permanently') }}"
                                                class="module-icon-button hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @else
                                            <a href="{{ route('leads.show', $lead->id) }}" wire:navigate title="{{ __('View') }}" aria-label="{{ __('View') }}"
                                                class="module-icon-button hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <a href="{{ route('leads.edit', $lead->id) }}" wire:navigate title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"
                                                class="module-icon-button hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <button wire:click="delete({{ $lead->id }})" wire:loading.attr="disabled" wire:target="delete({{ $lead->id }})" wire:confirm="{{ __('Move this lead to trash?') }}" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"
                                                class="module-icon-button hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <x-ui.empty-state
                                        :icon="$showTrashed ? 'trash' : 'lead'"
                                        :title="$showTrashed ? __('Trash is empty.') : __('No leads found.')"
                                        :message="$showTrashed ? __('Deleted leads will appear here.') : __('Create the first lead or adjust filters to see pipeline records here.')"
                                    >
                                    @unless($showTrashed)
                                        <x-button.primary-link href="{{ route('leads.create') }}" wire:navigate>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            {{ __('New Lead') }}
                                        </x-button.primary-link>
                                    @endunless
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="module-mobile-list">
                @forelse ($leads as $lead)
                    <div class="module-mobile-card">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-100 to-purple-100 dark:from-primary-900 dark:to-purple-900 flex items-center justify-center shrink-0">
                                    <span class="text-sm font-bold text-primary-600 dark:text-primary-400">{{ strtoupper(substr($lead->name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ $lead->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $lead->company ?: __('No company') }}</p>
                                </div>
                            </div>
                            <x-ui.status-chip :status="$lead->status" class="shrink-0">{{ __($lead->status) }}</x-ui.status-chip>
                        </div>
                        <div class="grid grid-cols-2 gap-1 text-xs text-gray-500 dark:text-gray-400 mb-3">
                            <span>{{ $lead->email ?: __('No email') }}</span>
                            <span>{{ $lead->phone ?: __('No phone') }}</span>
                            <span>{{ __('Source') }}: {{ $lead->source ? __($lead->source) : __('Unspecified') }}</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ format_currency($lead->value) }}</span>
                        </div>
                        <div class="flex gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/50">
                            @if ($showTrashed)
                                <button wire:click="restore({{ $lead->id }})" wire:loading.attr="disabled" wire:target="restore({{ $lead->id }})" class="flex-1 rounded-lg bg-emerald-50 py-2 text-xs font-semibold text-emerald-600 disabled:pointer-events-none disabled:opacity-50 dark:bg-emerald-900/30 dark:text-emerald-400"><x-ui.loading-label target="restore({{ $lead->id }})" :label="__('Restore')" :loading="__('Restoring...')" /></button>
                                <button wire:click="forceDelete({{ $lead->id }})" wire:loading.attr="disabled" wire:target="forceDelete({{ $lead->id }})" wire:confirm="{{ __('Permanently delete this lead?') }}" class="flex-1 rounded-lg bg-rose-50 py-2 text-xs font-semibold text-rose-600 disabled:pointer-events-none disabled:opacity-50 dark:bg-rose-900/30 dark:text-rose-400"><x-ui.loading-label target="forceDelete({{ $lead->id }})" :label="__('Delete')" :loading="__('Deleting...')" /></button>
                            @else
                                <a href="{{ route('leads.show', $lead->id) }}" wire:navigate class="flex-1 text-center rounded-lg bg-gray-50 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300">{{ __('View') }}</a>
                                <a href="{{ route('leads.edit', $lead->id) }}" wire:navigate class="flex-1 text-center rounded-lg bg-amber-50 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400">{{ __('Edit') }}</a>
                                <button wire:click="delete({{ $lead->id }})" wire:loading.attr="disabled" wire:target="delete({{ $lead->id }})" wire:confirm="{{ __('Move this lead to trash?') }}" class="flex-1 rounded-lg bg-white border border-gray-200 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 disabled:pointer-events-none disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800"><x-ui.loading-label target="delete({{ $lead->id }})" :label="__('Trash')" :loading="__('Moving...')" /></button>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state
                        :icon="$showTrashed ? 'trash' : 'lead'"
                        :title="$showTrashed ? __('Trash is empty.') : __('No leads found.')"
                        :message="$showTrashed ? __('Deleted leads will appear here.') : __('Create the first lead or adjust filters to see pipeline records here.')"
                        size="compact"
                    />
                @endforelse
            </div>

            <div class="module-pagination">
                {{ $leads->links() }}
            </div>
        </div>
    </div>
</div>
