<div class="module-page">
    <div class="module-container">
        <div class="module-header">
            <div>
                <h1 class="module-title">{{ __('Customers') }}</h1>
                <p class="module-subtitle">
                    {{ $showTrashed ? __('Review deleted customers and recover account records when needed.') : __('Manage active accounts and jump directly into billing or proposal work.') }}
                </p>
            </div>
            <div class="module-actions">
                <button wire:click="$toggle('showTrashed')" class="inline-flex items-center rounded-xl border px-4 py-2 text-sm font-medium transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $showTrashed ? 'border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 focus:ring-rose-500 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                    {{ $showTrashed ? __('Back to Active Customers') : __('Open Trash') }}
                </button>
                @unless ($showTrashed)
                    <x-button.primary-link href="{{ route('customers.create') }}" wire:navigate>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('New Customer') }}
                    </x-button.primary-link>
                @endunless
            </div>
        </div>

        <div class="module-panel">
            <div class="module-filter">
                <div class="relative max-w-md">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Search name, email, phone, company...') }}" class="block w-full rounded-xl border-gray-300 pl-10 text-sm shadow-sm transition focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950/50 dark:text-gray-100 dark:focus:ring-primary-500 dark:focus:border-primary-500">
                </div>
            </div>

            <!-- Desktop View: Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="module-table">
                    <thead class="module-table-head">
                        <tr>
                            <th class="module-table-heading">
                                <button wire:click="sortBy('name')" class="group inline-flex items-center gap-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    {{ __('Customer') }}
                                    <svg class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" /></svg>
                                </button>
                            </th>
                            <th class="module-table-heading">{{ __('Contact') }}</th>
                            <th class="module-table-heading-center">{{ __('Invoices') }}</th>
                            <th class="module-table-heading-center">{{ __('Proposals') }}</th>
                            <th class="module-table-heading-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="module-table-body">
                        @forelse ($customers as $customer)
                            <tr class="module-table-row group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 bg-gradient-to-br from-primary-100 to-purple-100 dark:from-primary-900 dark:to-purple-900 rounded-full flex items-center justify-center transform group-hover:scale-105 transition-transform">
                                            <span class="text-primary-600 dark:text-primary-300 font-bold">{{ substr($customer->name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $customer->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $customer->company ?: __('Individual') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-gray-300 flex items-center gap-1"><svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> {{ $customer->email ?: __('-') }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1"><svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> {{ $customer->phone ?: __('-') }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        {{ $customer->invoices_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                                        {{ $customer->proposals_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        @if ($showTrashed)
                                            <button wire:click="restore({{ $customer->id }})" class="module-icon-button hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30" title="{{ __('Restore') }}">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                            </button>
                                            <button wire:click="forceDelete({{ $customer->id }})" wire:confirm="{{ __('Permanently delete this customer?') }}" class="module-icon-button hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30" title="{{ __('Delete Permanently') }}">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        @else
                                            <a href="{{ route('customers.show', $customer->id) }}" wire:navigate class="module-icon-button hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30" title="{{ __('View') }}">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </a>
                                            <a href="{{ route('customers.edit', $customer->id) }}" wire:navigate class="module-icon-button hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30" title="{{ __('Edit') }}">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </a>
                                            <button wire:click="delete({{ $customer->id }})" wire:confirm="{{ __('Move this customer to trash?') }}" class="module-icon-button hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30" title="{{ __('Delete') }}">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <x-ui.empty-state
                                        :icon="$showTrashed ? 'trash' : 'customer'"
                                        :title="$showTrashed ? __('Trash is empty.') : __('No customers found.')"
                                        :message="$showTrashed ? __('Deleted customers will appear here.') : __('Create the first customer or clear the search to see account records here.')"
                                    >
                                        @unless($showTrashed)
                                            <x-button.primary-link href="{{ route('customers.create') }}" wire:navigate>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                {{ __('New Customer') }}
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
                @forelse ($customers as $customer)
                    <div class="module-mobile-card relative overflow-hidden">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 flex-shrink-0 bg-gradient-to-br from-primary-100 to-purple-100 dark:from-primary-900 dark:to-purple-900 rounded-full flex items-center justify-center">
                                    <span class="text-primary-600 dark:text-primary-300 font-bold">{{ substr($customer->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $customer->name }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $customer->company ?: __('Individual') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <svg class="mr-2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $customer->email ?: __('None') }}
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <svg class="mr-2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $customer->phone ?: __('None') }}
                            </div>
                        </div>

                        <div class="flex gap-4 border-t border-gray-100 dark:border-gray-700/50 py-3 mb-3">
                            <div class="flex-1 text-center">
                                <div class="text-xs text-gray-500 uppercase tracking-widest">{{ __('Invoices') }}</div>
                                <div class="font-bold text-gray-900 dark:text-white">{{ $customer->invoices_count }}</div>
                            </div>
                            <div class="flex-1 text-center border-l border-gray-100 dark:border-gray-700/50">
                                <div class="text-xs text-gray-500 uppercase tracking-widest">{{ __('Proposals') }}</div>
                                <div class="font-bold text-gray-900 dark:text-white">{{ $customer->proposals_count }}</div>
                            </div>
                        </div>

                        <div class="flex justify-center gap-2 pt-2">
                            @if ($showTrashed)
                                <button wire:click="restore({{ $customer->id }})" class="flex-1 rounded-lg bg-emerald-50 py-2 text-sm font-semibold text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">{{ __('Restore') }}</button>
                                <button wire:click="forceDelete({{ $customer->id }})" wire:confirm="{{ __('Permanently delete this customer?') }}" class="flex-1 rounded-lg bg-rose-50 py-2 text-sm font-semibold text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">{{ __('Delete') }}</button>
                            @else
                                <a href="{{ route('customers.show', $customer->id) }}" wire:navigate class="flex-1 text-center rounded-lg bg-gray-50 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('View') }}</a>
                                <a href="{{ route('customers.edit', $customer->id) }}" wire:navigate class="flex-1 text-center rounded-lg bg-amber-50 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400">{{ __('Edit') }}</a>
                                <button wire:click="delete({{ $customer->id }})" wire:confirm="{{ __('Move this customer to trash?') }}" class="flex-1 rounded-lg bg-white border border-gray-200 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">{{ __('Trash') }}</button>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state
                        :icon="$showTrashed ? 'trash' : 'customer'"
                        :title="$showTrashed ? __('Trash is empty.') : __('No customers found.')"
                        :message="$showTrashed ? __('Deleted customers will appear here.') : __('Create the first customer or clear the search to see account records here.')"
                        size="compact"
                    />
                @endforelse
            </div>

            <div class="module-pagination">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
