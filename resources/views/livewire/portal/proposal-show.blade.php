<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-primary-600 dark:text-primary-300">{{ __('Proposal') }}</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 dark:text-white">{{ $proposal->subject }}</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('Proposal #:number from :company', ['number' => $proposal->number, 'company' => $companyName]) }}</p>
        </div>
        <x-ui.status-chip :status="$proposal->status">{{ __($proposal->status) }}</x-ui.status-chip>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_300px]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="prose prose-slate max-w-none dark:prose-invert">
                {!! nl2br(e($proposal->content)) !!}
            </div>
        </section>

        <aside class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Total') }}</p>
                <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white">{{ format_currency($proposal->total) }}</p>
                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">{{ __('Review the proposal and respond when ready.') }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-black text-slate-950 dark:text-white">{{ __('Respond') }}</h2>
                <div class="mt-4 grid gap-3">
                    <button type="button" wire:click="accept" wire:loading.attr="disabled" wire:target="accept" class="rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 disabled:pointer-events-none disabled:opacity-60">
                        <x-ui.loading-label target="accept" :label="__('Accept Proposal')" :loading="__('Saving...')" />
                    </button>
                    <button type="button" wire:click="reject" wire:loading.attr="disabled" wire:target="reject" class="rounded-xl bg-rose-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-rose-700 disabled:pointer-events-none disabled:opacity-60">
                        <x-ui.loading-label target="reject" :label="__('Reject Proposal')" :loading="__('Saving...')" />
                    </button>
                </div>
            </div>
        </aside>
    </div>
</div>
