<div class="detail-page">
    <div class="work-container-narrow">
        {{-- Page Header --}}
        <div class="work-header">
            <div>
                <p class="work-kicker">{{ __('Invoices') }}</p>
                <h1 class="work-heading">{{ __('Invoice') }} {{ $invoice->number }}</h1>
                <p class="work-subtitle">
                    {{ __('Issued on') }} {{ format_date($invoice->invoice_date) }}
                </p>
            </div>
            <div class="work-actions">
                <x-button.secondary-link href="{{ route('invoices.index') }}" wire:navigate>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    {{ __('Back') }}
                </x-button.secondary-link>
                <a href="{{ route('invoices.pdf', $invoice->id) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-900/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ __('Download PDF') }}
                </a>
                <x-button.primary-link href="{{ route('invoices.edit', $invoice->id) }}" wire:navigate>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    {{ __('Edit') }}
                </x-button.primary-link>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 animate-fade-in flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-300">
                <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="text-sm font-medium">{{ session('message') }}</p>
            </div>
        @endif

        {{-- Invoice Document --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="p-8 sm:p-12">
                {{-- Top Section --}}
                <div class="flex flex-col sm:flex-row justify-between gap-8 mb-12">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $companyName }}</h2>
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line">
                            {{ $companyAddress }}
                        </div>
                    </div>
                    <div class="text-left sm:text-right">
                        <div class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ __('INVOICE') }}</div>
                        <div class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">#{{ $invoice->number }}</div>
                        <div class="mt-4 inline-flex">
                            <x-ui.status-chip :status="$invoice->status" class="text-sm px-3 py-1">{{ __($invoice->status) }}</x-ui.status-chip>
                        </div>
                    </div>
                </div>

                {{-- Bill To & Details --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-12">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">{{ __('Bill To') }}</h3>
                        @if ($invoice->customer)
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $invoice->customer->name }}</div>
                            @if ($invoice->customer->company)
                                <div class="text-sm text-gray-600 dark:text-gray-300">{{ $invoice->customer->company }}</div>
                            @endif
                            @if ($invoice->customer->address)
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ $invoice->customer->address }}</div>
                            @endif
                        @else
                            <div class="text-sm text-gray-500 italic">{{ __('Deleted Customer') }}</div>
                        @endif
                    </div>
                    <div class="sm:text-right text-sm">
                        <div class="grid grid-cols-2 gap-y-2 gap-x-4 sm:block sm:space-y-2">
                            <div class="flex sm:justify-end gap-2">
                                <span class="font-medium text-gray-500 dark:text-gray-400">{{ __('Invoice Date:') }}</span>
                                <span class="text-gray-900 dark:text-white">{{ format_date($invoice->invoice_date) }}</span>
                            </div>
                            <div class="flex sm:justify-end gap-2">
                                <span class="font-medium text-gray-500 dark:text-gray-400">{{ __('Due Date:') }}</span>
                                <span class="text-gray-900 dark:text-white font-semibold">{{ format_date($invoice->due_date) }}</span>
                            </div>
                            @if ($invoice->is_recurring)
                            <div class="flex sm:justify-end gap-2">
                                <span class="font-medium text-purple-500 dark:text-purple-400">{{ __('Recurring:') }}</span>
                                <span class="text-purple-900 dark:text-purple-300 font-semibold uppercase text-xs tracking-wider self-center">{{ $invoice->recurring_cycle }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 mb-8">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-950">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Description') }}</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-24">{{ __('Qty') }}</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-32">{{ __('Price') }}</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-32">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse($invoice->items as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $item->description }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-500 dark:text-gray-400">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-500 dark:text-gray-400">{{ format_currency($item->unit_price) }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-medium text-gray-900 dark:text-white">{{ format_currency($item->amount) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        <x-ui.empty-state
                                            icon="invoice"
                                            :title="__('No items found for this invoice.')"
                                            :message="__('Add invoice items so totals and PDF output are useful for the customer.')"
                                            size="compact"
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Totals --}}
                <div class="flex justify-end">
                    <div class="w-full sm:w-80 space-y-3">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                            <span>{{ __('Subtotal') }}</span>
                            <span class="font-medium">{{ format_currency($invoice->subtotal) }}</span>
                        </div>
                        @if ($invoice->tax_total > 0)
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                            <span>{{ __('Tax') }}</span>
                            <span class="font-medium">{{ format_currency($invoice->tax_total) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-200 dark:border-gray-800 pt-3 text-lg font-bold text-gray-900 dark:text-white">
                            <span>{{ __('Total') }}</span>
                            <span>{{ format_currency($invoice->total) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 dark:border-gray-800 pt-3 text-sm font-bold {{ $invoice->balance_due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            <span>{{ __('Balance Due') }}</span>
                            <span>{{ format_currency($invoice->balance_due) }}</span>
                        </div>
                    </div>
                </div>

                @if ($invoice->notes)
                {{-- Notes --}}
                <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">{{ __('Notes') }}</h3>
                    <div class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $invoice->notes }}</div>
                </div>
                @endif
            </div>

            {{-- Payments Section --}}
            <div class="bg-gray-50 dark:bg-gray-950/50 p-8 border-t border-gray-200 dark:border-gray-800">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Payments') }}</h3>
                    @if ($invoice->balance_due > 0)
                        <x-button.secondary wire:click="$set('showPaymentModal', true)" wire:loading.attr="disabled">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Record Payment') }}
                        </x-button.secondary>
                    @endif
                </div>

                @if($invoice->payments->count() > 0)
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-white dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Date') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Method') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Notes') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                @foreach($invoice->payments as $payment)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ format_date($payment->payment_date) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $payment->payment_method }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $payment->notes }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-emerald-600 dark:text-emerald-400">{{ format_currency($payment->amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-ui.empty-state
                        icon="invoice"
                        :title="__('No payments recorded yet.')"
                        :message="__('Record a payment when the customer pays, so the balance stays accurate.')"
                        size="compact"
                    />
                @endif
            </div>
        </div>
    </div>

    {{-- Payment Modal --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4" role="dialog" aria-modal="true" aria-labelledby="record-payment-title">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-md border border-gray-200 dark:border-gray-800 outline-none" tabindex="-1">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
                    <h3 id="record-payment-title" class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Record Payment') }}</h3>
                    <button type="button" aria-label="{{ __('Close') }}" wire:click="$set('showPaymentModal', false)" wire:loading.attr="disabled" wire:target="recordPayment" class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-gray-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form wire:submit="recordPayment">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Amount') }}</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                                </div>
                                <input type="number" step="0.01" wire:model="paymentAmount" class="block w-full rounded-xl border-gray-300 pl-7 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white" required>
                            </div>
                            @error('paymentAmount') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Payment Date') }}</label>
                            <input type="date" wire:model="paymentDate" class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white" required>
                            @error('paymentDate') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Payment Method') }}</label>
                            <select wire:model="paymentMethod" class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                <option value="Bank Transfer">{{ __('Bank Transfer') }}</option>
                                <option value="Credit Card">{{ __('Credit Card') }}</option>
                                <option value="Cash">{{ __('Cash') }}</option>
                                <option value="PayPal">{{ __('PayPal') }}</option>
                                <option value="Other">{{ __('Other') }}</option>
                            </select>
                            @error('paymentMethod') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Notes (Optional)') }}</label>
                            <textarea wire:model="paymentNotes" rows="2" class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white"></textarea>
                            @error('paymentNotes') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-800 flex justify-end gap-3 rounded-b-2xl">
                        <x-button.secondary type="button" wire:click="$set('showPaymentModal', false)" wire:loading.attr="disabled" wire:target="recordPayment">{{ __('Cancel') }}</x-button.secondary>
                        <x-button.primary type="submit" wire:loading.attr="disabled" wire:target="recordPayment">
                            <x-ui.loading-label target="recordPayment" :label="__('Save Payment')" :loading="__('Saving...')" />
                        </x-button.primary>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
