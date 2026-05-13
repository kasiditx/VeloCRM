<div class="min-h-screen bg-gray-50 dark:bg-gray-950">
    <div class="mx-auto max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">{{ __('Owner Brief') }}</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                    @php
                        $hour = now()->hour;
                        if ($hour < 12) echo __('Good Morning');
                        elseif ($hour < 17) echo __('Good Afternoon');
                        else echo __('Good Evening');
                    @endphp,
                    <span class="text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
                </h1>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    {{ $todayLabel }}. {{ __('Start with the decision that protects cash, follow-up, or today’s workload.') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('leads.create') }}" wire:navigate
                   class="inline-flex h-10 items-center gap-2 rounded-xl bg-primary-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-950">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('New Lead') }}
                </a>
                <a href="{{ route('tasks.create') }}" wire:navigate
                   class="inline-flex h-10 items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-950">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    {{ __('New Task') }}
                </a>
            </div>
        </div>

        <section class="grid gap-4 lg:grid-cols-3" aria-label="{{ __('Recommended decisions') }}">
            @foreach ($decisionCards as $card)
                @php
                    $tone = match ($card['tone']) {
                        'danger' => 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-900/60 dark:bg-rose-950/35 dark:text-rose-100',
                        'warning' => 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/35 dark:text-amber-100',
                        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/35 dark:text-emerald-100',
                        'info' => 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-900/60 dark:bg-sky-950/35 dark:text-sky-100',
                        default => 'border-gray-200 bg-white text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100',
                    };
                @endphp
                <article class="rounded-2xl border p-5 shadow-sm {{ $tone }}">
                    <p class="text-xs font-semibold uppercase tracking-wider opacity-70">{{ __('Recommended Action') }}</p>
                    <h2 class="mt-2 text-lg font-bold">{{ $card['title'] }}</h2>
                    <p class="mt-2 text-sm leading-6 opacity-80">{{ $card['message'] }}</p>
                    <a href="{{ $card['href'] }}" wire:navigate
                       class="mt-4 inline-flex items-center rounded-xl bg-white/80 px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-950/70 dark:text-gray-100 dark:hover:bg-gray-950">
                        {{ $card['action'] }}
                    </a>
                </article>
            @endforeach
        </section>

        @php
            $metrics = [
                [
                    'label' => __('Total Leads'),
                    'value' => number_format($totalLeads),
                    'detail' => __(':rate conversion rate', ['rate' => $conversionRate . '%']),
                    'tone' => 'primary',
                    'href' => route('leads.index'),
                    'icon' => '<svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                    'iconBg' => 'bg-primary-100 dark:bg-primary-900/40',
                ],
                [
                    'label' => __('Customers'),
                    'value' => number_format($totalCustomers),
                    'detail' => __(':count proposals tracked', ['count' => number_format($totalProposals)]),
                    'tone' => 'emerald',
                    'href' => route('customers.index'),
                    'icon' => '<svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                    'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/40',
                ],
                [
                    'label' => __('Paid Revenue'),
                    'value' => format_currency($totalRevenue),
                    'detail' => __('Paid invoice revenue'),
                    'tone' => 'sky',
                    'href' => route('invoices.index'),
                    'icon' => '<svg class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                    'iconBg' => 'bg-sky-100 dark:bg-sky-900/40',
                ],
                [
                    'label' => __('Pending Invoices'),
                    'value' => number_format($pendingInvoices),
                    'detail' => __(':amount outstanding', ['amount' => format_currency($pendingInvoiceBalance)]),
                    'tone' => 'rose',
                    'href' => route('invoices.index'),
                    'icon' => '<svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'iconBg' => 'bg-rose-100 dark:bg-rose-900/40',
                ],
            ];
        @endphp

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($metrics as $metric)
                <a href="{{ $metric['href'] }}" wire:navigate
                   class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-primary-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-primary-900 dark:hover:bg-gray-800/60 dark:focus:ring-offset-gray-950 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl {{ $metric['iconBg'] }} flex items-center justify-center shrink-0">
                        {!! $metric['icon'] !!}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ $metric['label'] }}</p>
                        <p class="mt-0.5 text-2xl font-bold text-gray-900 dark:text-white">{{ $metric['value'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-3 border-b border-gray-100 p-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Revenue by Month') }}</h2>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ __('Paid invoices over the last 12 months') }}</p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 ring-1 ring-inset ring-primary-600/10 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-500/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                        {{ __('Paid Revenue') }}
                    </span>
                </div>
                <div class="h-72 p-5">
                    <canvas id="revenue-chart" aria-label="{{ __('Revenue by Month') }}" role="img"></canvas>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 p-5 dark:border-gray-800">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Lead Pipeline') }}</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ __('Current lead count by status') }}</p>
                </div>
                <div class="h-72 p-5">
                    <canvas id="pipeline-chart" aria-label="{{ __('Lead Pipeline') }}" role="img"></canvas>
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-4 border-b border-gray-100 p-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Recent Activity') }}</h2>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ __('Latest recorded events across the CRM') }}</p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        {{ $recentActivity->count() }} {{ __('records') }}
                    </span>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($recentActivity as $activity)
                        @php
                            $desc = $activity->description ?? '';
                            $isCreate = str_contains($desc, 'created');
                            $isDelete = str_contains($desc, 'deleted');
                            $activityTone = $isCreate
                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20'
                                : ($isDelete
                                    ? 'bg-rose-50 text-rose-700 ring-rose-600/10 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20'
                                    : 'bg-primary-50 text-primary-700 ring-primary-600/10 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-500/20');
                        @endphp
                        <div class="flex items-start gap-3 px-5 py-4 transition hover:bg-gray-50 dark:hover:bg-gray-800/40">
                            <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset {{ $activityTone }}">
                                @if($isCreate)
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M12 4v16m8-8H4"/></svg>
                                @elseif($isDelete)
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16"/></svg>
                                @else
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold leading-5 text-gray-900 dark:text-gray-100">
                                    {{ ucfirst($activity->description ?: __('Activity recorded')) }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    @if($activity->causer)
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $activity->causer->name }}</span>
                                        <span aria-hidden="true"> / </span>
                                    @endif
                                    {{ $activity->created_at?->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state
                            icon="report"
                            :title="__('No recent activity found.')"
                            :message="__('Create leads, customers, invoices, or tasks to build the timeline.')"
                        />
                    @endforelse
                </div>
            </section>

            <div class="space-y-6">
                <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3 border-b border-gray-100 p-5 dark:border-gray-800">
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Upcoming Tasks') }}</h2>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ __('Due within the next 7 days') }}</p>
                        </div>
                        <a href="{{ route('tasks.index') }}" wire:navigate class="rounded-lg px-2 py-1 text-xs font-semibold text-primary-600 transition hover:bg-primary-50 hover:text-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-primary-400 dark:hover:bg-primary-500/10">
                            {{ __('View all') }}
                        </a>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($upcomingTasks as $task)
                            @php
                                $priority = strtolower($task->priority ?? 'medium');
                                $priorityClasses = match($priority) {
                                    'high', 'urgent' => 'bg-rose-50 text-rose-700 ring-rose-600/10 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20',
                                    'low' => 'bg-sky-50 text-sky-700 ring-sky-600/10 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20',
                                    default => 'bg-amber-50 text-amber-700 ring-amber-600/10 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20',
                                };
                            @endphp
                            <a href="{{ route('tasks.edit', $task->id) }}" wire:navigate class="block px-5 py-4 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:hover:bg-gray-800/40">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $task->title }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $task->assignee?->name ?? __('Unassigned') }} / {{ format_date($task->due_date) }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $priorityClasses }}">
                                        {{ __($task->priority) }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <x-ui.empty-state
                                icon="task"
                                :title="__('No upcoming tasks.')"
                                :message="__('Create a task with a due date to keep the next follow-up visible.')"
                                size="compact"
                            >
                                <a href="{{ route('tasks.create') }}" wire:navigate class="inline-flex text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">{{ __('New Task') }}</a>
                            </x-ui.empty-state>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3 border-b border-gray-100 p-5 dark:border-gray-800">
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Overdue Invoices') }}</h2>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ __('Invoices past due and still unpaid') }}</p>
                        </div>
                        <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-600/10 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20">
                            {{ format_currency($overdueInvoiceTotal) }}
                        </span>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($overdueInvoices as $invoice)
                            <a href="{{ route('invoices.index') }}" wire:navigate class="block px-5 py-4 transition hover:bg-rose-50/60 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-rose-500 dark:hover:bg-rose-500/10">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $invoice->number }}</p>
                                        <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">{{ $invoice->customer?->name ?? __('Unknown customer') }} / {{ format_date($invoice->due_date) }}</p>
                                    </div>
                                    <p class="shrink-0 text-sm font-bold text-rose-600 dark:text-rose-400">{{ $invoice->money($invoice->balance_due) }}</p>
                                </div>
                            </a>
                        @empty
                            <x-ui.empty-state
                                icon="invoice"
                                :title="__('No overdue invoices.')"
                                :message="__('All unpaid invoices are still within their due dates.')"
                                size="compact"
                            />
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>

    @script
        <script>
            (() => {
                const revenueCanvas = document.getElementById('revenue-chart');
                const pipelineCanvas = document.getElementById('pipeline-chart');

                if (!revenueCanvas || !pipelineCanvas || typeof Chart === 'undefined') {
                    return;
                }

                const revenueLabels = @json($revenueSeries->pluck('label'));
                const revenueValues = @json($revenueSeries->pluck('total'));
                const pipelineLabels = @json($leadPipeline->map(fn ($item) => __($item['label']))->values());
                const pipelineValues = @json($leadPipeline->pluck('total'));
                const isDark = document.documentElement.classList.contains('dark');
                const tickColor = isDark ? '#9ca3af' : '#6b7280';
                const gridColor = isDark ? 'rgba(156, 163, 175, 0.12)' : 'rgba(107, 114, 128, 0.16)';
                const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--color-primary-600').trim() || '#4f46e5';
                const surfaceColor = isDark ? '#111827' : '#fffffe';

                if (window.dashboardRevenueChart) window.dashboardRevenueChart.destroy();
                if (window.dashboardPipelineChart) window.dashboardPipelineChart.destroy();

                window.dashboardRevenueChart = new Chart(revenueCanvas, {
                    type: 'bar',
                    data: {
                        labels: revenueLabels,
                        datasets: [{
                            label: @json(__('Paid Revenue')),
                            data: revenueValues,
                            borderRadius: 8,
                            backgroundColor: primaryColor,
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.dataset.label}: ${context.formattedValue}`,
                                },
                            },
                        },
                        scales: {
                            x: {
                                ticks: { color: tickColor, font: { size: 11 } },
                                grid: { display: false },
                                border: { display: false },
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { color: tickColor, font: { size: 11 } },
                                grid: { color: gridColor },
                                border: { display: false },
                            },
                        },
                    },
                });

                window.dashboardPipelineChart = new Chart(pipelineCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: pipelineLabels,
                        datasets: [{
                            data: pipelineValues,
                            backgroundColor: ['#4f46e5', '#0284c7', '#d97706', '#059669', '#e11d48'],
                            borderWidth: 3,
                            borderColor: surfaceColor,
                            hoverBorderColor: surfaceColor,
                        }]
                    },
                    options: {
                        cutout: '68%',
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: tickColor,
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    borderRadius: 5,
                                    useBorderRadius: true,
                                    font: { size: 12 },
                                },
                            },
                        },
                    },
                });
            })();
        </script>
    @endscript
</div>
