<div class="module-page">
    <div class="module-container">
        <div class="module-header">
            <div>
                <h1 class="module-title">{{ __('Proposals') }}</h1>
                <p class="module-subtitle">
                    {{ $showTrashed ? __('Restore or permanently remove deleted proposals.') : __('Track proposal status for both prospects and customers.') }}
                </p>
            </div>
            <div class="module-actions">
                <button wire:click="$toggle('showTrashed')" wire:loading.attr="disabled" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-55 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 {{ $showTrashed ? 'border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 focus:ring-rose-500 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300' : '' }}">
                    {{ $showTrashed ? __('Back to Active Proposals') : __('Open Trash') }}
                </button>
                @unless ($showTrashed)
                    <x-button.primary-link href="{{ route('proposals.create') }}" wire:navigate>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('New Proposal') }}
                    </x-button.primary-link>
                @endunless
            </div>
        </div>

        @if (session()->has('message'))
            <div class="animate-fade-in flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-300">
                <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="text-sm font-medium">{{ session('message') }}</p>
            </div>
        @endif

        <div class="module-panel">
            <!-- Desktop View: Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="module-table">
                    <thead class="module-table-head">
                        <tr>
                            <th class="module-table-heading">{{ __('Number') }}</th>
                            <th class="module-table-heading">{{ __('Subject') }}</th>
                            <th class="module-table-heading">{{ __('Related To') }}</th>
                            <th class="module-table-heading-right">{{ __('Total') }}</th>
                            <th class="module-table-heading-center">{{ __('Status') }}</th>
                            <th class="module-table-heading-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="module-table-body">
                        @forelse($proposals as $proposal)
                            <tr class="module-table-row group">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-lg bg-primary-50 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </div>
                                        {{ $proposal->number }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $proposal->subject }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if($proposal->customer)
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/20">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                            {{ $proposal->customer->name }}
                                        </span>
                                    @elseif($proposal->lead)
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-orange-50 px-2.5 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20 dark:bg-orange-400/10 dark:text-orange-400 dark:ring-orange-400/20">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                            {{ $proposal->lead->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic">{{ __('No linked record') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ format_currency($proposal->total) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <x-ui.status-chip :status="$proposal->status">{{ __($proposal->status) }}</x-ui.status-chip>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        @if ($showTrashed)
                                            <button wire:click="restore({{ $proposal->id }})" wire:loading.attr="disabled" wire:target="restore({{ $proposal->id }})" class="module-icon-button hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30" title="{{ __('Restore') }}" aria-label="{{ __('Restore') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11a4 4 0 110 8H9m-6-8l4-4m-4 4l4 4"></path></svg>
                                            </button>
                                            <button wire:click="forceDelete({{ $proposal->id }})" wire:loading.attr="disabled" wire:target="forceDelete({{ $proposal->id }})" data-velo-confirm="{{ __('Permanently delete this proposal?') }}" class="module-icon-button hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30" title="{{ __('Delete Permanently') }}" aria-label="{{ __('Delete Permanently') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        @else
                                            <a href="{{ route('proposals.show', $proposal->id) }}" wire:navigate class="module-icon-button hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30" title="{{ __('View') }}" aria-label="{{ __('View') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <a href="{{ route('proposals.edit', $proposal->id) }}" wire:navigate class="module-icon-button hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            <button wire:click="delete({{ $proposal->id }})" wire:loading.attr="disabled" wire:target="delete({{ $proposal->id }})" data-velo-confirm="{{ __('Move this proposal to trash?') }}" class="module-icon-button hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <x-ui.empty-state
                                        :icon="$showTrashed ? 'trash' : 'proposal'"
                                        :title="$showTrashed ? __('Trash is empty.') : __('No proposals found.')"
                                        :message="$showTrashed ? __('Deleted proposals will appear here.') : __('Create a proposal to keep pricing and next steps with the right lead or customer.')"
                                    >
                                        @unless($showTrashed)
                                            <x-button.primary-link href="{{ route('proposals.create') }}" wire:navigate>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                {{ __('New Proposal') }}
                                            </x-button.primary-link>
                                        @endunless
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile View: Cards -->
            <div class="module-mobile-list">
                @forelse($proposals as $proposal)
                    <div class="module-mobile-card">
                        <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-3 dark:border-gray-700/50">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-primary-600 dark:text-primary-400">{{ $proposal->number }}</span>
                            </div>
                            <x-ui.status-chip :status="$proposal->status">{{ __($proposal->status) }}</x-ui.status-chip>
                        </div>

                        <h3 class="font-bold text-gray-900 dark:text-white mb-2">{{ $proposal->subject }}</h3>

                        <div class="flex items-center justify-between text-sm mb-4">
                            <div class="text-gray-500 dark:text-gray-400">
                                @if($proposal->customer)
                                    <span class="text-xs flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg> {{ $proposal->customer->name }}</span>
                                @elseif($proposal->lead)
                                    <span class="text-xs flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg> {{ $proposal->lead->name }}</span>
                                @else
                                    <span class="text-xs italic">{{ __('No linked record') }}</span>
                                @endif
                            </div>
                            <span class="font-bold text-gray-900 dark:text-gray-100">
                                {{ format_currency($proposal->total) }}
                            </span>
                        </div>

                        <div class="flex gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/50">
                            @if ($showTrashed)
                                <button wire:click="restore({{ $proposal->id }})" wire:loading.attr="disabled" wire:target="restore({{ $proposal->id }})" class="flex-1 rounded-lg bg-emerald-50 py-2 text-sm font-semibold text-emerald-600 disabled:pointer-events-none disabled:opacity-50 dark:bg-emerald-900/30 dark:text-emerald-400"><x-ui.loading-label target="restore({{ $proposal->id }})" :label="__('Restore')" :loading="__('Restoring...')" /></button>
                                <button wire:click="forceDelete({{ $proposal->id }})" wire:loading.attr="disabled" wire:target="forceDelete({{ $proposal->id }})" data-velo-confirm="{{ __('Permanently delete this proposal?') }}" class="flex-1 rounded-lg bg-rose-50 py-2 text-sm font-semibold text-rose-600 disabled:pointer-events-none disabled:opacity-50 dark:bg-rose-900/30 dark:text-rose-400"><x-ui.loading-label target="forceDelete({{ $proposal->id }})" :label="__('Delete Permanently')" :loading="__('Deleting...')" /></button>
                            @else
                                <a href="{{ route('proposals.show', $proposal->id) }}" wire:navigate class="flex-1 text-center rounded-lg bg-gray-50 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('View') }}</a>
                                <a href="{{ route('proposals.edit', $proposal->id) }}" wire:navigate class="flex-1 text-center rounded-lg bg-amber-50 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400">{{ __('Edit') }}</a>
                                <button wire:click="delete({{ $proposal->id }})" wire:loading.attr="disabled" wire:target="delete({{ $proposal->id }})" data-velo-confirm="{{ __('Move this proposal to trash?') }}" class="flex-1 rounded-lg bg-white border border-gray-200 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 disabled:pointer-events-none disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"><x-ui.loading-label target="delete({{ $proposal->id }})" :label="__('Trash')" :loading="__('Moving...')" /></button>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state
                        :icon="$showTrashed ? 'trash' : 'proposal'"
                        :title="$showTrashed ? __('Trash is empty.') : __('No proposals found.')"
                        :message="$showTrashed ? __('Deleted proposals will appear here.') : __('Create a proposal to keep pricing and next steps with the right lead or customer.')"
                        size="compact"
                    />
                @endforelse
            </div>

            <div class="module-pagination">
                {{ $proposals->links() }}
            </div>
        </div>
    </div>
</div>
