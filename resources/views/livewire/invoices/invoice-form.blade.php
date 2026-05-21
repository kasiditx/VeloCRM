<div class="form-page">
    <div class="work-container">
        <div class="work-header">
            <div>
                <p class="work-kicker">{{ __('Invoices') }}</p>
                <h1 class="work-heading">{{ $invoiceId ? __('Edit Invoice') : __('New Invoice') }}</h1>
                <p class="work-subtitle">{{ __('Build line-item invoices with tax support and recurring billing options.') }}</p>
            </div>
            <x-button.secondary-link href="{{ route('invoices.index') }}" wire:navigate>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to invoices') }}
            </x-button.secondary-link>
        </div>

        <form wire:submit="save" class="form-panel" data-draft-key="velocrm.invoice-form.{{ $invoiceId ?: 'new' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Left Side --}}
                <div class="space-y-5">
                    <div>
                        <label class="field-label">{{ __('Customer') }}</label>
                        <select wire:model="customer_id" class="field-control">
                            <option value="">{{ __('Select Customer') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->company }})</option>
                            @endforeach
                        </select>
                        @error('customer_id') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">{{ __('Invoice Date') }}</label>
                            <input type="date" wire:model="invoice_date" class="field-control">
                            @error('invoice_date') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="field-label">{{ __('Due Date') }}</label>
                            <input type="date" wire:model="due_date" class="field-control">
                            @error('due_date') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="field-label">{{ __('Status') }}</label>
                        <select wire:model="status" class="field-control">
                            <option value="Draft">{{ __('Draft') }}</option>
                            <option value="Sent">{{ __('Sent') }}</option>
                            <option value="Partially Paid">{{ __('Partially Paid') }}</option>
                            <option value="Paid">{{ __('Paid') }}</option>
                            <option value="Overdue">{{ __('Overdue') }}</option>
                            <option value="Cancelled">{{ __('Cancelled') }}</option>
                        </select>
                        @error('status') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">{{ __('Invoice Currency') }}</label>
                            <select wire:model.live="currency" class="field-control">
                                @foreach($currencyOptions as $code)
                                    <option value="{{ $code }}">{{ $code }}</option>
                                @endforeach
                            </select>
                            @error('currency') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="field-label">{{ __('Exchange Rate') }}</label>
                            <input type="number" step="0.000001" min="0.000001" wire:model="exchange_rate" class="field-control">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Base currency conversion rate for dashboard and reports.') }}</p>
                            @error('exchange_rate') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-3">
                            <input type="checkbox" wire:model.live="is_recurring" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-950">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Enable Recurring Billing') }}</span>
                        </label>
                    </div>

                    @if($is_recurring)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">{{ __('Cycle') }}</label>
                            <select wire:model="recurring_cycle" class="field-control">
                                <option value="weekly">{{ __('Weekly') }}</option>
                                <option value="monthly">{{ __('Monthly') }}</option>
                                <option value="yearly">{{ __('Yearly') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">{{ __('Next Date') }}</label>
                            <input type="date" wire:model="next_recurring_date" class="field-control">
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Right Side --}}
                <div class="space-y-5">
                    <div>
                        <label class="field-label">{{ __('Document Type') }}</label>
                        <select wire:model.live="document_type" class="field-control">
                            @foreach($documentTypes as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('document_type') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="field-label">{{ __('Invoice Number') }}</label>
                        <input type="text" wire:model="number" class="field-control">
                        @error('number') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="field-label">{{ __('Private Notes') }}</label>
                        <textarea wire:model="notes" rows="4" class="field-control"></textarea>
                    </div>
                </div>
            </div>

            <x-custom-fields.form-fields :fields="$customFields" />

            {{-- Items Table --}}
            <div class="border-t border-gray-200 dark:border-gray-800 pt-6">
                <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Invoice Items') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Search catalog items, reuse recent invoice lines, or add custom items.') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-button.secondary type="button" wire:click="openCatalogModal">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            {{ __('Choose products/services') }}
                        </x-button.secondary>
                        <x-button.secondary type="button" wire:click="addFromLatestProposal">
                            {{ __('Add from quotation') }}
                        </x-button.secondary>
                        <x-button.secondary type="button" wire:click="copyLatestItems">
                            {{ __('Copy latest items') }}
                        </x-button.secondary>
                        <x-button.secondary type="button" wire:click="addStandardItem">
                            {{ __('Add standard item') }}
                        </x-button.secondary>
                        <button type="button" wire:click="clearItems" class="inline-flex items-center rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-900/50">
                            {{ __('Clear all items') }}
                        </button>
                    </div>
                </div>

                @error('items') <p class="mb-3 field-error">{{ $message }}</p> @enderror

                @if(empty($items))
                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center dark:border-gray-700 dark:bg-gray-950">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('No invoice items yet.') }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Choose products/services to start, or add a custom item.') }}</p>
                        <div class="mt-4 flex justify-center gap-2">
                            <x-button.primary type="button" wire:click="openCatalogModal">{{ __('Choose products/services') }}</x-button.primary>
                            <x-button.secondary type="button" wire:click="addItem">{{ __('Add custom item') }}</x-button.secondary>
                        </div>
                    </div>
                @else
                <div class="invoice-items-table rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="hidden border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-950 xl:grid xl:grid-cols-[minmax(300px,1fr)_100px_132px_132px_144px_48px] xl:gap-2 2xl:grid-cols-[minmax(360px,1fr)_130px_170px_170px_190px_48px] 2xl:gap-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Item') }}</div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Qty') }}</div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Unit Price') }}</div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Tax') }}</div>
                        <div class="text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Amount') }}</div>
                        <div></div>
                    </div>

                    <div class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($items as $index => $item)
                            <div wire:key="invoice-item-{{ $item['_key'] ?? $index }}" class="invoice-item-row relative grid grid-cols-1 gap-3 px-4 py-3 xl:grid-cols-[minmax(300px,1fr)_100px_132px_132px_144px_48px] xl:items-start xl:gap-2 2xl:grid-cols-[minmax(360px,1fr)_130px_170px_170px_190px_48px] 2xl:gap-3">
                                <div class="min-w-0">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 xl:hidden">{{ __('Item') }}</label>
                                    @php
                                        $catalogQuery = trim((string) ($item['catalog_query'] ?? ''));
                                        $catalogMatches = $this->itemCatalogMatches($index);
                                    @endphp
                                    <div
                                        class="autocomplete-wrapper relative w-full"
                                        x-data="{ open: false }"
                                        @click.outside="open = false"
                                        @keydown.escape.window="open = false"
                                    >
                                        <input
                                            type="text"
                                            wire:model.live.debounce.300ms="items.{{ $index }}.catalog_query"
                                            placeholder="{{ __('Search product or service...') }}"
                                            title="{{ $item['catalog_query'] ?? $item['description'] ?? '' }}"
                                            class="h-10 w-full truncate rounded-lg border-gray-300 pr-28 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"
                                            x-ref="itemInput"
                                            @focus="open = true"
                                            @input="open = true"
                                            @keydown.enter.prevent="$wire.commitItemQuery({{ $index }}, $event.target.value); open = false"
                                        >
                                        @if(! empty($item['catalog_code']))
                                            <span class="pointer-events-none absolute right-8 top-2 rounded-md bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-300">{{ $item['catalog_code'] }}</span>
                                        @endif
                                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-gray-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                                        </span>

                                        <div
                                            x-cloak
                                            x-show="open"
                                            class="autocomplete-dropdown absolute left-0 right-0 top-[calc(100%+6px)] z-[9999] max-h-[280px] min-w-full overflow-y-auto rounded-xl border border-gray-200 bg-white p-1 shadow-2xl dark:border-gray-700 dark:bg-gray-950"
                                        >
                                            @forelse($catalogMatches as $match)
                                                <button type="button" wire:click="selectCatalogItem({{ $index }}, '{{ $match['key'] }}')" @click="open = false" class="block w-full rounded-lg px-3 py-2 text-left text-sm transition hover:bg-primary-50 dark:hover:bg-primary-950/40">
                                                    <span class="block whitespace-normal font-semibold text-gray-900 dark:text-gray-100">{{ $match['name'] }}</span>
                                                    <span class="mt-0.5 block whitespace-normal text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $match['code'] ?? $match['sku'] ?? __('Recent') }}
                                                        <span aria-hidden="true">&middot;</span>
                                                        {{ $match['description'] }}
                                                        <span aria-hidden="true">&middot;</span>
                                                        {{ velocrm_money($match['unit_price'] ?? 0, ($match['currency'] ?? null) ?: $currency) }}
                                                    </span>
                                                </button>
                                            @empty
                                                <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">{{ __('No products/services found.') }}</div>
                                            @endforelse

                                            @if($catalogQuery !== '')
                                                <button type="button" @click="$wire.useCustomItemQuery({{ $index }}, $refs.itemInput.value); open = false" class="mt-1 block w-full rounded-lg border border-dashed border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 transition hover:border-primary-300 hover:bg-primary-50 dark:border-gray-700 dark:text-gray-200 dark:hover:border-primary-700 dark:hover:bg-primary-950/40">
                                                    {{ __('Use ":text" as a custom item', ['text' => $catalogQuery]) }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    @error('items.'.$index.'.description') <p class="field-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 xl:hidden">{{ __('Qty') }}</label>
                                    <div class="flex rounded-lg shadow-sm">
                                        <button type="button" wire:click="decrementQuantity({{ $index }})" class="rounded-l-lg border border-r-0 border-gray-300 px-2 text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800">-</button>
                                        <input type="number" min="0.01" step="0.01" wire:model.live="items.{{ $index }}.quantity" class="w-full border-gray-300 text-center text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                                        <button type="button" wire:click="incrementQuantity({{ $index }})" class="rounded-r-lg border border-l-0 border-gray-300 px-2 text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800">+</button>
                                    </div>
                                    @error('items.'.$index.'.quantity') <p class="field-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 xl:hidden">{{ __('Unit Price') }}</label>
                                    <input type="text" inputmode="decimal" wire:model.live.debounce.300ms="items.{{ $index }}.unit_price" placeholder="68,000.00" class="w-full rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                                    @error('items.'.$index.'.unit_price') <p class="field-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 xl:hidden">{{ __('Tax') }}</label>
                                    <select wire:model.live="items.{{ $index }}.tax_option" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                                        @foreach($taxOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @if(($item['wht_amount'] ?? 0) > 0)
                                        <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">-{{ velocrm_money($item['wht_amount'], $currency) }}</p>
                                    @endif
                                </div>

                                <div class="text-left xl:text-right">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 xl:hidden">{{ __('Amount') }}</label>
                                    <div class="rounded-lg bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-900 dark:bg-gray-950 dark:text-gray-100 xl:bg-transparent xl:px-0">
                                        {{ velocrm_money($item['amount'], $currency) }}
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-1 xl:block xl:text-center">
                                    <button type="button" wire:click="duplicateItem({{ $index }})" title="{{ __('Duplicate row') }}" class="p-1 text-gray-400 hover:text-primary-600 transition-colors rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/30">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v10a2 2 0 002 2h7M8 7V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V17a2 2 0 01-2 2h-1M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h7a2 2 0 002-2v-2"/></svg>
                                    </button>
                                    <button type="button" wire:click="removeItem({{ $index }})" wire:loading.attr="disabled" wire:target="removeItem({{ $index }})" title="{{ __('Remove row') }}" class="p-1 text-gray-400 hover:text-rose-600 transition-colors rounded-lg hover:bg-rose-50 disabled:pointer-events-none disabled:opacity-45 dark:hover:bg-rose-900/30">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-4 flex flex-wrap gap-2">
                    <x-button.secondary wire:click="addItem" type="button">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Add custom item') }}
                    </x-button.secondary>
                </div>
            </div>

            @if($catalogModalOpen)
                <div class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/50 px-4 py-6 sm:items-center" role="dialog" aria-modal="true">
                    <div class="w-full max-w-3xl rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ __('Choose products/services') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Search by name, SKU, code, or description. Select several items and add them at once.') }}</p>
                                </div>
                                <button type="button" wire:click="closeCatalogModal" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <input type="search" wire:model.live.debounce.300ms="catalogSearch" placeholder="{{ __('Search product or service...') }}" class="mt-4 w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        </div>
                        <div class="max-h-[55vh] overflow-y-auto p-3">
                            @forelse($catalogItems as $catalogItem)
                                <button type="button" wire:click="toggleCatalogSelection('{{ $catalogItem['key'] }}')" class="mb-2 flex w-full items-start gap-3 rounded-xl border px-3 py-3 text-left transition {{ ($selectedCatalogItems[$catalogItem['key']] ?? false) ? 'border-primary-300 bg-primary-50 dark:border-primary-800 dark:bg-primary-950/40' : 'border-gray-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800' }}">
                                    <span class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded border {{ ($selectedCatalogItems[$catalogItem['key']] ?? false) ? 'border-primary-600 bg-primary-600 text-white' : 'border-gray-300 dark:border-gray-700' }}">
                                        @if($selectedCatalogItems[$catalogItem['key']] ?? false)
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-semibold text-gray-900 dark:text-gray-100">{{ $catalogItem['name'] }}</span>
                                        <span class="mt-0.5 block text-sm text-gray-500 dark:text-gray-400">{{ $catalogItem['description'] }}</span>
                                        <span class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ $catalogItem['code'] ?? $catalogItem['sku'] ?? __('Recent') }}</span>
                                            @if($catalogItem['unit'])
                                                <span>{{ __('Unit') }}: {{ $catalogItem['unit'] }}</span>
                                            @endif
                                            <span>{{ velocrm_money($catalogItem['unit_price'], $catalogItem['currency'] ?: $currency) }}</span>
                                        </span>
                                    </span>
                                </button>
                            @empty
                                <div class="rounded-xl border border-dashed border-gray-300 px-6 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    {{ __('No catalog match. Add a custom item instead.') }}
                                </div>
                            @endforelse
                        </div>
                        <div class="flex flex-col gap-2 border-t border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input type="checkbox" id="merge-catalog-items" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950">
                                {{ __('Merge duplicate catalog items') }}
                            </label>
                            <div class="flex justify-end gap-2">
                                <x-button.secondary type="button" wire:click="closeCatalogModal">{{ __('Cancel') }}</x-button.secondary>
                                <button type="button" x-data @click="$wire.addSelectedCatalogItems(document.getElementById('merge-catalog-items').checked)" class="inline-flex items-center justify-center rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                    {{ __('Add selected items') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Totals --}}
            <div class="flex justify-end border-t border-gray-200 dark:border-gray-800 pt-5">
                <div class="w-72 space-y-3">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                        <span>{{ __('Subtotal') }}</span>
                        <span class="font-medium">{{ velocrm_money($subtotal, $currency) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm text-gray-600 dark:text-gray-300">
                        <span>{{ __('Tax') }}</span>
                        <select wire:model.live="tax_id" class="rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 text-sm py-1">
                            <option value="">{{ __('No Tax') }}</option>
                            @foreach($taxTemplates as $tax)
                                <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                        <span>{{ __('Tax Amount') }}</span>
                        <span class="font-medium">{{ velocrm_money($tax_total, $currency) }}</span>
                    </div>
                    @if($wht_total > 0)
                        <div class="flex justify-between text-sm text-rose-600 dark:text-rose-400">
                            <span>{{ __('Withholding Tax') }}</span>
                            <span class="font-medium">-{{ velocrm_money($wht_total, $currency) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-800 pt-3">
                        <span>{{ __('Net Total') }}</span>
                        <span>{{ velocrm_money($total, $currency) }}</span>
                    </div>
                </div>
            </div>

            {{-- Footer Buttons --}}
            <div class="form-footer">
                <x-button.secondary-link href="{{ route('invoices.index') }}" wire:navigate>
                    {{ __('Cancel') }}
                </x-button.secondary-link>
                <x-button.primary type="submit" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $invoiceId ? __('Update Invoice') : __('Create Invoice') }}</span>
                    <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                </x-button.primary>
            </div>
        </form>
    </div>
</div>
