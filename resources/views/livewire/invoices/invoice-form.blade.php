<div class="form-page">
    <div class="work-container-form">
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

        <form wire:submit="save" class="form-panel">
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

            {{-- Items Table --}}
            <div class="border-t border-gray-200 dark:border-gray-800 pt-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Invoice Items') }}</h3>
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-950">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Description') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-24">{{ __('Qty') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-32">{{ __('Price') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-32">{{ __('Amount') }}</th>
                                <th class="px-4 py-3 w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($items as $index => $item)
                                <tr wire:key="item-{{ $index }}">
                                    <td class="px-4 py-3">
                                        <input type="text" wire:model.live.debounce.500ms="items.{{ $index }}.description" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 text-sm">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" wire:model.live="items.{{ $index }}.quantity" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 text-sm">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" wire:model.live="items.{{ $index }}.unit_price" step="0.01" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 text-sm">
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ format_currency($item['amount']) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" wire:click="removeItem({{ $index }})" wire:loading.attr="disabled" wire:target="removeItem({{ $index }})" class="p-1 text-gray-400 hover:text-rose-600 transition-colors rounded-lg hover:bg-rose-50 disabled:pointer-events-none disabled:opacity-45 dark:hover:bg-rose-900/30">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <x-button.secondary wire:click="addItem" type="button">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Add Item') }}
                    </x-button.secondary>
                </div>
            </div>

            {{-- Totals --}}
            <div class="flex justify-end border-t border-gray-200 dark:border-gray-800 pt-5">
                <div class="w-72 space-y-3">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                        <span>{{ __('Subtotal') }}</span>
                        <span class="font-medium">{{ format_currency($subtotal) }}</span>
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
                        <span class="font-medium">{{ format_currency($tax_total) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-800 pt-3">
                        <span>{{ __('Total') }}</span>
                        <span>{{ format_currency($total) }}</span>
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
