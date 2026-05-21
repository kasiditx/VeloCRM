<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-primary-600 dark:text-primary-300">{{ __('Billing') }}</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 dark:text-white">{{ __('Your invoices') }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500 dark:text-slate-400">{{ __('View invoice status, balances, payments, and download official PDF documents.') }}</p>
        </div>
        <div class="relative w-full sm:w-80">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Search invoice number...') }}" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800 dark:bg-slate-900 dark:text-white">
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold">{{ __('Invoice') }}</th>
                        <th class="px-6 py-4 text-left font-bold">{{ __('Date') }}</th>
                        <th class="px-6 py-4 text-right font-bold">{{ __('Total') }}</th>
                        <th class="px-6 py-4 text-right font-bold">{{ __('Balance') }}</th>
                        <th class="px-6 py-4 text-left font-bold">{{ __('Status') }}</th>
                        <th class="px-6 py-4 text-right font-bold">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="px-6 py-4 text-sm font-bold text-primary-700 dark:text-primary-300">{{ $invoice->number }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ format_date($invoice->invoice_date) }}</td>
                            <td class="px-6 py-4 text-right text-sm font-semibold">{{ $invoice->money($invoice->total) }}</td>
                            <td class="px-6 py-4 text-right text-sm font-semibold {{ $invoice->balance_due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $invoice->money($invoice->balance_due) }}</td>
                            <td class="px-6 py-4"><x-ui.status-chip :status="$invoice->status">{{ __($invoice->status) }}</x-ui.status-chip></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('portal.invoices.show', $invoice->id) }}" wire:navigate class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">{{ __('View') }}</a>
                                    <a href="{{ route('portal.invoices.pdf', ['invoice' => $invoice->id, 'locale' => app()->getLocale()]) }}" target="_blank" class="rounded-xl bg-primary-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-primary-700">PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <x-ui.empty-state icon="invoice" :title="__('No invoices yet.')" :message="__('Your invoices will appear here once they are issued.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800 md:hidden">
            @forelse($invoices as $invoice)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-bold text-primary-700 dark:text-primary-300">{{ $invoice->number }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ format_date($invoice->invoice_date) }}</p>
                        </div>
                        <x-ui.status-chip :status="$invoice->status">{{ __($invoice->status) }}</x-ui.status-chip>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div><p class="text-xs text-slate-500">{{ __('Total') }}</p><p class="font-bold">{{ $invoice->money($invoice->total) }}</p></div>
                        <div><p class="text-xs text-slate-500">{{ __('Balance') }}</p><p class="font-bold {{ $invoice->balance_due > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $invoice->money($invoice->balance_due) }}</p></div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('portal.invoices.show', $invoice->id) }}" wire:navigate class="flex-1 rounded-xl bg-slate-100 py-2 text-center text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ __('View') }}</a>
                        <a href="{{ route('portal.invoices.pdf', ['invoice' => $invoice->id, 'locale' => app()->getLocale()]) }}" target="_blank" class="flex-1 rounded-xl bg-primary-600 py-2 text-center text-xs font-bold text-white">PDF</a>
                    </div>
                </div>
            @empty
                <div class="p-6"><x-ui.empty-state icon="invoice" :title="__('No invoices yet.')" :message="__('Your invoices will appear here once they are issued.')" size="compact" /></div>
            @endforelse
        </div>

        <div class="border-t border-slate-100 px-6 py-4 dark:border-slate-800">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
