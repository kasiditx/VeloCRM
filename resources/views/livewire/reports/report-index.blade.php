<div class="report-page">
    <div class="work-container">

        {{-- Page Header --}}
        <div class="work-header">
            <div>
                <p class="work-kicker">{{ __('Insights') }}</p>
                <h1 class="work-heading">{{ __('Reports') }}</h1>
                <p class="work-subtitle">{{ __('Use this range to decide what needs attention: cash collection, lead conversion, or acquisition focus.') }}</p>
            </div>
            <div class="work-actions">
                <button wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <x-ui.loading-label target="exportCsv" :label="__('Export CSV')" :loading="__('Exporting...')" />
                </button>
            </div>
        </div>

        <section class="grid gap-4 lg:grid-cols-3" aria-label="{{ __('Decision summary') }}">
            @foreach ($decisionNotes as $note)
                @php
                    $tone = match ($note['tone']) {
                        'danger' => 'border-rose-200 bg-rose-50 dark:border-rose-900/60 dark:bg-rose-950/35',
                        'warning' => 'border-amber-200 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/35',
                        'success' => 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/35',
                        'info' => 'border-sky-200 bg-sky-50 dark:border-sky-900/60 dark:bg-sky-950/35',
                        default => 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900',
                    };
                @endphp
                <article class="rounded-2xl border p-5 shadow-sm {{ $tone }}">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Decision Point') }}</p>
                    <h2 class="mt-2 text-lg font-bold text-gray-900 dark:text-gray-100">{{ $note['title'] }}</h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $note['message'] }}</p>
                    <a href="{{ $note['href'] }}" wire:navigate
                       class="mt-4 inline-flex items-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-950/70 dark:text-gray-100 dark:ring-gray-800 dark:hover:bg-gray-950">
                        {{ $note['action'] }}
                    </a>
                </article>
            @endforeach
        </section>

        {{-- Date Range Filter --}}
        <div class="form-panel">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="field-label">{{ __('Start Date') }}</label>
                    <input wire:model="startDate" type="date" class="field-control">
                    @error('startDate') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('End Date') }}</label>
                    <input wire:model="endDate" type="date" class="field-control">
                    @error('endDate') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2 xl:col-span-2 flex items-end">
                    <button wire:click="applyFilters" wire:loading.attr="disabled" wire:target="applyFilters"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <x-ui.loading-label target="applyFilters" :label="__('Apply Filters')" :loading="__('Applying...')" />
                    </button>
                </div>
            </div>
        </div>

        {{-- KPI Cards Row 1 --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="report-metric flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="report-metric-label">{{ __('Revenue') }}</p>
                    <p class="report-metric-value">{{ format_currency($stats['Revenue']) }}</p>
                </div>
            </div>
            <div class="report-metric flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="report-metric-label">{{ __('Invoices') }}</p>
                    <p class="report-metric-value">{{ $stats['Invoices'] }}</p>
                </div>
            </div>
            <div class="report-metric flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="report-metric-label">{{ __('Leads') }}</p>
                    <p class="report-metric-value">{{ $stats['Leads'] }}</p>
                </div>
            </div>
            <div class="report-metric flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <p class="report-metric-label">{{ __('Conversion Rate') }}</p>
                    <p class="report-metric-value">{{ $stats['Conversion Rate'] }}</p>
                </div>
            </div>
        </div>

        {{-- KPI Cards Row 2 --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="report-metric flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <p class="report-metric-label">{{ __('Customers') }}</p>
                    <p class="report-metric-value">{{ $stats['Customers'] }}</p>
                </div>
            </div>
            <div class="report-metric flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-900/40 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="report-metric-label">{{ __('Proposals') }}</p>
                    <p class="report-metric-value">{{ $stats['Proposals'] }}</p>
                </div>
            </div>
            <div class="report-metric flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <div>
                    <p class="report-metric-label">{{ __('Open Tasks') }}</p>
                    <p class="report-metric-value">{{ $stats['Open Tasks'] }}</p>
                </div>
            </div>
            <div class="report-metric flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                </div>
                <div>
                    <p class="report-metric-label">{{ __('Average Paid Invoice') }}</p>
                    <p class="report-metric-value">{{ format_currency($stats['Average Invoice']) }}</p>
                </div>
            </div>
        </div>

        {{-- Charts Row 1 --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="report-card">
                <h2 class="report-section-title">{{ __('Revenue by Month') }}</h2>
                <div class="space-y-2">
                    @forelse ($revenueByMonth as $month)
                        <div class="report-row">
                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ $month['label'] }}</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ format_currency($month['amount']) }}</span>
                        </div>
                    @empty
                        <x-ui.empty-state
                            icon="report"
                            :title="__('No paid invoices in the selected range.')"
                            :message="__('Adjust the date range or mark invoices as paid to build this report.')"
                            size="compact"
                        />
                    @endforelse
                </div>
            </div>
            <div class="report-card">
                <h2 class="report-section-title">{{ __('Revenue by Customer') }}</h2>
                <div class="space-y-2">
                    @forelse ($revenueByCustomer as $customer)
                        <div class="report-row">
                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ $customer['label'] }}</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ format_currency($customer['amount']) }}</span>
                        </div>
                    @empty
                        <x-ui.empty-state
                            icon="customer"
                            :title="__('No customer revenue in the selected range.')"
                            :message="__('Paid invoices will group by customer here once revenue is recorded.')"
                            size="compact"
                        />
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Charts Row 2 --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="report-card">
                <h2 class="report-section-title">{{ __('Lead Sources') }}</h2>
                <div class="space-y-2">
                    @forelse ($leadSources as $source)
                        <div class="report-row">
                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ $source['label'] }}</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $source['total'] }}</span>
                        </div>
                    @empty
                        <x-ui.empty-state
                            icon="lead"
                            :title="__('No lead source data in the selected range.')"
                            :message="__('Add lead sources or widen the date range to see where opportunities come from.')"
                            size="compact"
                        />
                    @endforelse
                </div>
            </div>
            <div class="report-card">
                <h2 class="report-section-title">{{ __('Lead Pipeline') }}</h2>
                <div class="space-y-2">
                    @foreach ($leadStatuses as $status)
                        <div class="report-row">
                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ __($status['label']) }}</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $status['total'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Conversion Summary --}}
        <div class="report-card">
            <h2 class="report-section-title">{{ __('Lead Conversion Summary') }}</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl bg-gray-50 px-5 py-5 dark:bg-gray-950/60">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Leads Created') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $conversion['total_leads'] }}</p>
                </div>
                <div class="rounded-xl bg-primary-50 px-5 py-5 dark:bg-primary-900/20">
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-400">{{ __('Converted Leads') }}</p>
                    <p class="mt-2 text-3xl font-bold text-primary-700 dark:text-primary-300">{{ $conversion['converted_leads'] }}</p>
                </div>
                <div class="rounded-xl bg-emerald-50 px-5 py-5 dark:bg-emerald-900/20">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">{{ __('Conversion Rate') }}</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-300">{{ $conversion['conversion_rate'] }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
