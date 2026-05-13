<div class="py-6 lg:py-8">
    <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">{{ __('This Month') }}</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ __('Calendar') }}</h1>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Manage your schedule, tasks, and follow up dates.') }}
                </p>
            </div>

            <div class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto lg:items-center">
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:w-[520px]">
                    <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Scheduled work') }}</p>
                        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ $summary['tasks'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Open invoices') }}</p>
                        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ $summary['invoices'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Overdue invoices') }}</p>
                        <p class="mt-1 text-xl font-bold {{ $summary['overdueInvoices'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">{{ $summary['overdueInvoices'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Days with activity') }}</p>
                        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ $summary['activeDays'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 border-b border-gray-100 p-5 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $currentMonthName }} <span class="font-semibold text-gray-500 dark:text-gray-400">{{ $currentYear }}</span>
                    </h2>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 ring-1 ring-inset ring-primary-600/10 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-500/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                            {{ __('Task') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            {{ __('Invoice due') }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2" role="group" aria-label="{{ __('Calendar controls') }}">
                    <button wire:click="previousMonth" type="button" aria-label="{{ __('Previous month') }}"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button wire:click="goToToday" type="button"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        {{ __('Today') }}
                    </button>
                    <button wire:click="nextMonth" type="button" aria-label="{{ __('Next month') }}"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
            </div>

            <div class="hidden md:block">
                <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-950">
                    @foreach([__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat')] as $dayName)
                        <div class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ $dayName }}
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-7">
                    @foreach($calendar as $day)
                        @php
                            $hasEvents = $day['tasks']->isNotEmpty() || $day['invoices']->isNotEmpty();
                            $hiddenEvents = max(0, $day['tasks']->count() - 3) + max(0, $day['invoices']->count() - 3);
                        @endphp
                        <div class="min-h-[142px] border-b border-r border-gray-100 p-2 transition-colors last:border-r-0 dark:border-gray-800 {{ $day['currentMonth'] ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/70 text-gray-400 dark:bg-gray-950/50 dark:text-gray-600' }} {{ $day['isToday'] ? 'ring-2 ring-inset ring-primary-500/70' : 'hover:bg-gray-50 dark:hover:bg-gray-800/40' }}">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="text-[11px] font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ $day['weekday'] }}</span>
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-sm font-semibold {{ $day['isToday'] ? 'bg-primary-600 text-white' : ($day['currentMonth'] ? 'text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-600') }}">
                                    {{ $day['day'] }}
                                </span>
                            </div>

                            <div class="space-y-1.5">
                                @foreach($day['tasks']->take(3) as $task)
                                    <a href="{{ route('tasks.edit', $task->id) }}"
                                        class="block rounded-lg bg-primary-50 px-2 py-1.5 text-xs font-medium text-primary-800 ring-1 ring-inset ring-primary-600/10 transition hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-primary-500/10 dark:text-primary-200 dark:ring-primary-500/20"
                                        title="{{ __('Open task') }}: {{ $task->title }}">
                                        <span class="block truncate">{{ $task->title }}</span>
                                        <span class="mt-0.5 block truncate text-[10px] font-semibold uppercase tracking-wide text-primary-500 dark:text-primary-300">{{ __($task->priority) }} · {{ __($task->status) }}</span>
                                    </a>
                                @endforeach

                                @foreach($day['invoices']->take(3) as $invoice)
                                    <a href="{{ route('invoices.edit', $invoice->id) }}"
                                        class="block rounded-lg bg-emerald-50 px-2 py-1.5 text-xs font-medium text-emerald-800 ring-1 ring-inset ring-emerald-600/10 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20"
                                        title="{{ __('Open invoice') }}: {{ $invoice->number }}">
                                        <span class="block truncate">{{ $invoice->number }}</span>
                                        <span class="mt-0.5 block truncate text-[10px] font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">{{ $invoice->customer?->name ?? __('Unknown customer') }} · {{ $invoice->money($invoice->balance_due ?? $invoice->total) }}</span>
                                    </a>
                                @endforeach

                                @if($hiddenEvents > 0)
                                    <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                                        +{{ $hiddenEvents }} {{ __('more') }}
                                    </span>
                                @elseif(! $hasEvents)
                                    <span class="block rounded-lg border border-dashed border-gray-200 px-2 py-3 text-center text-xs text-gray-400 dark:border-gray-800 dark:text-gray-600">
                                        {{ __('No scheduled items') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-3 p-4 md:hidden">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('Month agenda') }}</h3>
                @forelse($agendaDays as $day)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $day['fullDate'] }}</p>
                                @if($day['isToday'])
                                    <p class="text-xs font-semibold text-primary-600 dark:text-primary-400">{{ __('Today') }}</p>
                                @endif
                            </div>
                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ $day['tasks']->count() + $day['invoices']->count() }}
                            </span>
                        </div>

                        <div class="space-y-2">
                            @foreach($day['tasks'] as $task)
                                <a href="{{ route('tasks.edit', $task->id) }}" class="flex items-start gap-3 rounded-lg bg-primary-50 p-3 text-primary-900 transition hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-primary-500/10 dark:text-primary-100">
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-primary-500"></span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold">{{ $task->title }}</span>
                                        <span class="mt-0.5 block text-xs text-primary-600 dark:text-primary-300">{{ __('Task') }} · {{ __($task->priority) }} · {{ __($task->status) }}</span>
                                    </span>
                                </a>
                            @endforeach

                            @foreach($day['invoices'] as $invoice)
                                <a href="{{ route('invoices.edit', $invoice->id) }}" class="flex items-start gap-3 rounded-lg bg-emerald-50 p-3 text-emerald-900 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:bg-emerald-500/10 dark:text-emerald-100">
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold">{{ $invoice->number }} · {{ $invoice->money($invoice->balance_due ?? $invoice->total) }}</span>
                                        <span class="mt-0.5 block text-xs text-emerald-700 dark:text-emerald-300">{{ __('Invoice due') }} · {{ $invoice->customer?->name ?? __('Unknown customer') }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state
                        icon="calendar"
                        :title="__('No scheduled activity this month.')"
                        :message="__('Create a task or invoice with a due date to see it here.')"
                        size="compact"
                    >
                        <x-button.primary-link href="{{ route('tasks.create') }}" wire:navigate>{{ __('New Task') }}</x-button.primary-link>
                    </x-ui.empty-state>
                @endforelse
            </div>
        </div>
    </div>
</div>
