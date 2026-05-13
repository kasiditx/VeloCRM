<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-primary-600 dark:text-primary-300">{{ __('Invoice') }}</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 dark:text-white">{{ $invoice->number }}</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('Issued by :company', ['company' => $companyName]) }}</p>
        </div>
        <a href="{{ route('public.invoice.pdf', $invoice->public_token) }}" target="_blank" class="rounded-xl bg-primary-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-primary-700">{{ __('Download PDF') }}</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="grid gap-4 sm:grid-cols-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</p>
                    <div class="mt-2"><x-ui.status-chip :status="$invoice->status">{{ __($invoice->status) }}</x-ui.status-chip></div>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Invoice Date') }}</p>
                    <p class="mt-2 font-bold text-slate-900 dark:text-white">{{ format_date($invoice->invoice_date) }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Due Date') }}</p>
                    <p class="mt-2 font-bold text-slate-900 dark:text-white">{{ format_date($invoice->due_date) }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Balance') }}</p>
                    <p class="mt-2 font-black {{ $invoice->balance_due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $invoice->money($invoice->balance_due) }}</p>
                </div>
            </div>

            <div class="mt-8 rounded-2xl bg-slate-50 p-4 dark:bg-slate-950/50">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Bill To') }}</p>
                <p class="mt-2 font-bold text-slate-950 dark:text-white">{{ $invoice->customer?->name ?? __('Customer') }}</p>
                @if ($invoice->customer?->company)
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $invoice->customer->company }}</p>
                @endif
                @if ($invoice->customer?->address)
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-500 dark:text-slate-400">{{ $invoice->customer->address }}</p>
                @endif
                @if ($invoice->tax_id)
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('Tax ID') }}: {{ $invoice->tax_id }}</p>
                @endif
                @if ($invoice->branch)
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Branch') }}: {{ $invoice->branch }}</p>
                @endif
            </div>

            <div class="mt-8 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-slate-500">
                            <th class="py-3 font-bold">{{ __('Description') }}</th>
                            <th class="py-3 text-right font-bold">{{ __('Qty') }}</th>
                            <th class="py-3 text-right font-bold">{{ __('Unit Price') }}</th>
                            <th class="py-3 text-right font-bold">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($invoice->items as $item)
                            <tr>
                                <td class="py-4 font-medium text-slate-900 dark:text-white">{{ $item->description }}</td>
                                <td class="py-4 text-right text-slate-500">{{ number_format((float) $item->quantity, 2) }}</td>
                                <td class="py-4 text-right text-slate-500">{{ $invoice->money($item->unit_price) }}</td>
                                <td class="py-4 text-right font-bold">{{ $invoice->money($item->amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-10 text-center text-sm text-slate-500">{{ __('No items found for this invoice.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-black text-slate-950 dark:text-white">{{ __('Payment Summary') }}</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Currency') }}</dt><dd class="font-bold">{{ $invoice->currency ?? velocrm_currency_code() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Subtotal') }}</dt><dd class="font-bold">{{ $invoice->money($invoice->subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Tax') }}</dt><dd class="font-bold">{{ $invoice->money($invoice->tax_total) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Discount') }}</dt><dd class="font-bold">{{ $invoice->money($invoice->discount) }}</dd></div>
                    <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                        <div class="flex justify-between text-base"><dt class="font-bold">{{ __('Total') }}</dt><dd class="font-black">{{ $invoice->money($invoice->total) }}</dd></div>
                    </div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Paid') }}</dt><dd class="font-bold text-emerald-600">{{ $invoice->money($invoice->amount_paid) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Balance Due') }}</dt><dd class="font-black text-rose-600">{{ $invoice->money($invoice->balance_due) }}</dd></div>
                </dl>
            </div>

            <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-900/50">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-500">{{ __('Payment') }}</h2>
                @if (session()->has('error'))
                    <p class="mt-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">{{ session('error') }}</p>
                @endif
                @if (session()->has('success'))
                    <p class="mt-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('success') }}</p>
                @endif
                @if($paymentStatus === 'manual' && $bankTransferInstructions)
                    <div class="mt-3 rounded-xl bg-white p-3 text-sm text-slate-700 shadow-sm dark:bg-slate-950 dark:text-slate-200">
                        <p class="font-bold">{{ __('Bank Transfer Instructions') }}</p>
                        <p class="mt-2 whitespace-pre-line">{{ $bankTransferInstructions }}</p>
                    </div>
                @endif
                @if((float) $invoice->balance_due > 0)
                    <a href="{{ $paymentUrl }}" class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-primary-600 px-4 py-3 text-sm font-black text-white transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                        {{ __('Pay with :gateway', ['gateway' => $paymentGatewayLabel]) }}
                    </a>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ __('You will be redirected to the configured payment flow. If Manual is selected, bank transfer instructions will be shown here.') }}</p>
                @else
                    <p class="mt-2 text-sm font-bold text-emerald-600 dark:text-emerald-300">{{ __('This invoice has been paid.') }}</p>
                @endif
            </div>
        </aside>
    </div>
</div>
