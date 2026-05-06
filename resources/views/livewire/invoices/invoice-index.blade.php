<div class="module-page">
    <div class="module-container">

        {{-- Page Header --}}
        <div class="module-header">
            <div>
                <h1 class="module-title">{{ __('Invoices') }}</h1>
                <p class="module-subtitle">
                    {{ $showTrashed ? __('Restore or permanently remove deleted invoices.') : __('Track billing status, balances, and recurring invoice history.') }}
                </p>
            </div>
            <div class="module-actions">
                <button wire:click="$toggle('showTrashed')" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 rounded-xl border px-4 py-2 text-sm font-medium shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-55
                        {{ $showTrashed
                            ? 'border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 focus:ring-rose-500 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300'
                            : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    {{ $showTrashed ? __('Back to Active') : __('Open Trash') }}
                </button>
                @unless ($showTrashed)
                    <x-button.primary-link href="{{ route('invoices.create') }}" wire:navigate>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('New Invoice') }}
                    </x-button.primary-link>
                @endunless
            </div>
        </div>

        {{-- Card panel --}}
        <div class="module-panel">

            {{-- Search filter --}}
            <div class="module-filter">
                <div class="relative max-w-md">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Search by number or customer...') }}"
                        class="block w-full rounded-xl border-gray-300 pl-9 text-sm shadow-sm transition focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950/50 dark:text-gray-100">
                </div>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="module-table">
                    <thead class="module-table-head">
                        <tr>
                            <th class="module-table-heading">{{ __('Number') }}</th>
                            <th class="module-table-heading">{{ __('Customer') }}</th>
                            <th class="module-table-heading">{{ __('Date') }}</th>
                            <th class="module-table-heading-right">{{ __('Total') }}</th>
                            <th class="module-table-heading-right">{{ __('Balance Due') }}</th>
                            <th class="module-table-heading">{{ __('Status') }}</th>
                            <th class="module-table-heading-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="module-table-body">
                        @forelse($invoices as $invoice)
                            <tr class="module-table-row group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-lg bg-primary-50 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400 shrink-0">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        </div>
                                        <div>
                                            <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ $invoice->number }}</span>
                                            @if($invoice->is_recurring)
                                                <span class="ml-1 text-xs bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 px-2 py-0.5 rounded-full">{{ __('Recurring') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $invoice->customer?->name ?? __('Deleted customer') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ format_date($invoice->invoice_date) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ format_currency($invoice->total) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-medium {{ $invoice->balance_due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ format_currency($invoice->balance_due) }}
                                </td>
                                <td class="px-6 py-4">
                                    <x-ui.status-chip :status="$invoice->status">{{ __($invoice->status) }}</x-ui.status-chip>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        @if ($showTrashed)
                                            <button wire:click="restore({{ $invoice->id }})" wire:loading.attr="disabled" wire:target="restore({{ $invoice->id }})" title="{{ __('Restore') }}" aria-label="{{ __('Restore') }}"
                                                class="module-icon-button hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </button>
                                            <button wire:click="forceDelete({{ $invoice->id }})" wire:loading.attr="disabled" wire:target="forceDelete({{ $invoice->id }})" wire:confirm="{{ __('Permanently delete this invoice?') }}" title="{{ __('Delete Permanently') }}" aria-label="{{ __('Delete Permanently') }}"
                                                class="module-icon-button hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @else
                                            <a href="{{ route('invoices.show', $invoice->id) }}" wire:navigate title="{{ __('View') }}" aria-label="{{ __('View') }}"
                                                class="module-icon-button hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <a href="{{ route('invoices.edit', $invoice->id) }}" wire:navigate title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"
                                                class="module-icon-button hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <a href="{{ route('invoices.pdf', $invoice->id) }}" target="_blank" title="PDF" aria-label="{{ __('Download PDF') }}"
                                                class="module-icon-button hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            </a>
                                            <button wire:click="delete({{ $invoice->id }})" wire:loading.attr="disabled" wire:target="delete({{ $invoice->id }})" wire:confirm="{{ __('Move this invoice to trash?') }}" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"
                                                class="module-icon-button hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <x-ui.empty-state
                                        :icon="$showTrashed ? 'trash' : 'invoice'"
                                        :title="$showTrashed ? __('Trash is empty.') : __('No invoices found.')"
                                        :message="$showTrashed ? __('Deleted invoices will appear here.') : __('Create the first invoice when a customer is ready to be billed.')"
                                    >
                                    @unless($showTrashed)
                                        <x-button.primary-link href="{{ route('invoices.create') }}" wire:navigate>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            {{ __('New Invoice') }}
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
                @forelse($invoices as $invoice)
                    <div class="module-mobile-card">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div>
                                <p class="font-semibold text-primary-600 dark:text-primary-400 text-sm">{{ $invoice->number }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $invoice->customer?->name ?? __('Deleted customer') }}</p>
                            </div>
                            <x-ui.status-chip :status="$invoice->status" class="shrink-0">{{ __($invoice->status) }}</x-ui.status-chip>
                        </div>
                        <div class="flex gap-4 text-xs text-gray-500 dark:text-gray-400 mb-3">
                            <span>{{ __('Date') }}: {{ format_date($invoice->invoice_date) }}</span>
                            <span>{{ __('Total') }}: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ format_currency($invoice->total) }}</span></span>
                            <span>{{ __('Due') }}: <span class="font-semibold {{ $invoice->balance_due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ format_currency($invoice->balance_due) }}</span></span>
                        </div>
                        <div class="flex gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/50">
                            @if ($showTrashed)
                                <button wire:click="restore({{ $invoice->id }})" wire:loading.attr="disabled" wire:target="restore({{ $invoice->id }})" class="flex-1 rounded-lg bg-emerald-50 py-2 text-xs font-semibold text-emerald-600 disabled:pointer-events-none disabled:opacity-50 dark:bg-emerald-900/30 dark:text-emerald-400"><x-ui.loading-label target="restore({{ $invoice->id }})" :label="__('Restore')" :loading="__('Restoring...')" /></button>
                                <button wire:click="forceDelete({{ $invoice->id }})" wire:loading.attr="disabled" wire:target="forceDelete({{ $invoice->id }})" wire:confirm="{{ __('Permanently delete this invoice?') }}" class="flex-1 rounded-lg bg-rose-50 py-2 text-xs font-semibold text-rose-600 disabled:pointer-events-none disabled:opacity-50 dark:bg-rose-900/30 dark:text-rose-400"><x-ui.loading-label target="forceDelete({{ $invoice->id }})" :label="__('Delete')" :loading="__('Deleting...')" /></button>
                            @else
                                <a href="{{ route('invoices.show', $invoice->id) }}" wire:navigate class="flex-1 text-center rounded-lg bg-gray-50 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('View') }}</a>
                                <a href="{{ route('invoices.edit', $invoice->id) }}" wire:navigate class="flex-1 text-center rounded-lg bg-amber-50 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400">{{ __('Edit') }}</a>
                                <a href="{{ route('invoices.pdf', $invoice->id) }}" target="_blank" class="flex-1 text-center rounded-lg bg-emerald-50 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400">PDF</a>
                                <button wire:click="delete({{ $invoice->id }})" wire:loading.attr="disabled" wire:target="delete({{ $invoice->id }})" wire:confirm="{{ __('Move this invoice to trash?') }}" class="flex-1 rounded-lg bg-white border border-gray-200 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 disabled:pointer-events-none disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800"><x-ui.loading-label target="delete({{ $invoice->id }})" :label="__('Trash')" :loading="__('Moving...')" /></button>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state
                        :icon="$showTrashed ? 'trash' : 'invoice'"
                        :title="$showTrashed ? __('Trash is empty.') : __('No invoices found.')"
                        :message="$showTrashed ? __('Deleted invoices will appear here.') : __('Create the first invoice when a customer is ready to be billed.')"
                        size="compact"
                    />
                @endforelse
            </div>

            <div class="module-pagination">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</div>
