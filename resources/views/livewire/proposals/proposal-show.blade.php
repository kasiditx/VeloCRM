<div class="detail-page">
    <div class="work-container-narrow">
        {{-- Page Header --}}
        <div class="work-header">
            <div>
                <p class="work-kicker">{{ __('Proposals') }}</p>
                <h1 class="work-heading">{{ __('Proposal') }} #{{ $proposal->id }} - {{ $proposal->subject }}</h1>
                <p class="work-subtitle">
                    {{ __('Created on') }} {{ format_date($proposal->created_at) }}
                </p>
            </div>
            <div class="work-actions">
                <x-button.secondary-link href="{{ route('proposals.index') }}" wire:navigate>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    {{ __('Back') }}
                </x-button.secondary-link>
                <a href="{{ route('proposals.pdf', $proposal->id) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-900/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ __('Download PDF') }}
                </a>
                <button type="button"
                    x-data="{ copied: false, link: @js($publicShareUrl) }"
                    x-on:click="navigator.clipboard.writeText(link).then(() => { copied = true; setTimeout(() => copied = false, 1800) })"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    aria-label="{{ __('Copy public share link') }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-6 8h8a2 2 0 002-2V8l-6-6H8a2 2 0 00-2 2v4"/></svg>
                    <span x-text="copied ? @js(__('Copied')) : @js(__('Copy Share Link'))"></span>
                </button>
                <x-button.primary-link href="{{ route('proposals.edit', $proposal->id) }}" wire:navigate>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    {{ __('Edit') }}
                </x-button.primary-link>
            </div>
        </div>

        {{-- Proposal Document --}}
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
                        <div class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ __('PROPOSAL') }}</div>
                        <div class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">#{{ $proposal->id }}</div>
                        <div class="mt-4 inline-flex">
                            <x-ui.status-chip :status="$proposal->status" class="text-sm px-3 py-1">{{ __($proposal->status) }}</x-ui.status-chip>
                        </div>
                    </div>
                </div>

                {{-- Proposal To & Details --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-12">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">{{ __('Prepared For') }}</h3>
                        @if ($proposal->customer)
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $proposal->customer->name }}</div>
                            @if ($proposal->customer->company)
                                <div class="text-sm text-gray-600 dark:text-gray-300">{{ $proposal->customer->company }}</div>
                            @endif
                        @elseif ($proposal->lead)
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $proposal->lead->name }}</div>
                            @if ($proposal->lead->company)
                                <div class="text-sm text-gray-600 dark:text-gray-300">{{ $proposal->lead->company }}</div>
                            @endif
                            <div class="mt-1 text-xs inline-block bg-primary-50 text-primary-700 px-2 py-0.5 rounded-full dark:bg-primary-900/40 dark:text-primary-300">{{ __('Lead') }}</div>
                        @else
                            <div class="text-sm text-gray-500 italic">{{ __('Deleted Record') }}</div>
                        @endif
                    </div>
                    <div class="sm:text-right text-sm">
                        <div class="grid grid-cols-2 gap-y-2 gap-x-4 sm:block sm:space-y-2">
                            <div class="flex sm:justify-end gap-2">
                                <span class="font-medium text-gray-500 dark:text-gray-400">{{ __('Date Issued:') }}</span>
                                <span class="text-gray-900 dark:text-white">{{ format_date($proposal->created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($proposal->content)
                {{-- Proposal Content --}}
                <div class="mb-12 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line bg-gray-50 dark:bg-gray-800/50 p-6 rounded-xl border border-gray-100 dark:border-gray-800">
                    {{ $proposal->content }}
                </div>
                @endif

                {{-- Totals --}}
                <div class="flex justify-end">
                    <div class="w-full sm:w-80 space-y-3">
                        <div class="flex justify-between border-t border-gray-200 dark:border-gray-800 pt-3 text-lg font-bold text-gray-900 dark:text-white">
                            <span>{{ __('Total') }}</span>
                            <span>{{ format_currency($proposal->total) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
