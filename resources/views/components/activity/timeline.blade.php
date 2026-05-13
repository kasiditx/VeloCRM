@props(['activities'])

<div class="divide-y divide-gray-100 dark:divide-gray-800">
    @forelse($activities as $activity)
        @php
            $tone = \App\Support\ActivityLogFormatter::eventTone($activity->event, $activity->description ?? '');
            $toneClass = match($tone) {
                'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20',
                'rose' => 'bg-rose-50 text-rose-700 ring-rose-600/10 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20',
                'sky' => 'bg-sky-50 text-sky-700 ring-sky-600/10 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20',
                default => 'bg-primary-50 text-primary-700 ring-primary-600/10 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-500/20',
            };
            $changes = \App\Support\ActivityLogFormatter::changedFields($activity);
        @endphp
        <article class="px-5 py-4 transition hover:bg-gray-50 dark:hover:bg-gray-800/40">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset {{ $toneClass }}">
                    @if($tone === 'emerald')
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M12 4v16m8-8H4"/></svg>
                    @elseif($tone === 'rose')
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16"/></svg>
                    @else
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    @endif
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold leading-5 text-gray-900 dark:text-gray-100">
                            {{ ucfirst($activity->description ?: __('Activity recorded')) }}
                        </p>
                        @if($activity->event)
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-bold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $activity->event }}</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @if($activity->causer)
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $activity->causer->name }}</span>
                            <span aria-hidden="true"> / </span>
                        @endif
                        {{ \App\Support\ActivityLogFormatter::subjectLabel($activity->subject_type) }}
                        @if($activity->subject_id)
                            #{{ $activity->subject_id }}
                        @endif
                        <span aria-hidden="true"> / </span>
                        {{ $activity->created_at?->diffForHumans() }}
                    </p>

                    @if(count($changes) > 0)
                        <div class="mt-3 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                            <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-800">
                                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900">
                                    @foreach($changes as $change)
                                        <tr>
                                            <td class="w-36 px-3 py-2 font-semibold text-gray-600 dark:text-gray-300">{{ $change['field'] }}</td>
                                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $change['old'] }}</td>
                                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $change['new'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <x-ui.empty-state
            icon="report"
            :title="__('No activity recorded yet.')"
            :message="__('New changes will appear in this timeline.')"
        />
    @endforelse
</div>
