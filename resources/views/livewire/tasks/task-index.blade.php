<div class="module-page">
    <div class="module-container">
        <div class="module-header">
            <div>
                <h1 class="module-title">{{ __('Tasks') }}</h1>
                <p class="module-subtitle">
                    {{ __('Manage your workflow and keep track of important tasks.') }}
                </p>
            </div>
            <div class="module-actions">
                <a href="{{ route('tasks.board') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
                    {{ __('Kanban Board') }}
                </a>
                <a href="{{ route('tasks.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('New Task') }}
                </a>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="animate-fade-in flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-300">
                <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="text-sm font-medium">{{ session('message') }}</p>
            </div>
        @endif

        <div class="module-panel">
            <!-- Desktop View: Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="module-table">
                    <thead class="module-table-head">
                        <tr>
                            <th class="module-table-heading">{{ __('Status') }}</th>
                            <th class="module-table-heading">{{ __('Title') }}</th>
                            <th class="module-table-heading">{{ __('Related To') }}</th>
                            <th class="module-table-heading">{{ __('Priority') }}</th>
                            <th class="module-table-heading">{{ __('Due Date') }}</th>
                            <th class="module-table-heading-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="module-table-body">
                        @forelse($tasks as $task)
                            <tr class="module-table-row group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-ui.status-chip :status="$task->status">
                                        @if($task->status === 'Completed')
                                            <svg class="-ml-0.5 mr-1.5 h-3 w-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                        @elseif($task->status === 'In Progress')
                                            <svg class="-ml-0.5 mr-1.5 h-3 w-3 animate-pulse" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                        @else
                                            <svg class="-ml-0.5 mr-1.5 h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @endif
                                        {{ __($task->status) }}
                                    </x-ui.status-chip>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $task->title }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if($task->relatable)
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20">
                                            {{ __(class_basename($task->relatable_type)) }}: {{ $task->relatable->name ?? $task->relatable->subject ?? '#' . $task->relatable->id }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-ui.status-chip :status="$task->priority">{{ __($task->priority) }}</x-ui.status-chip>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        {{ $task->due_date ? format_date($task->due_date) : __('No date') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('tasks.edit', $task->id) }}" class="module-icon-button hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <button wire:click="delete({{ $task->id }})" wire:loading.attr="disabled" wire:target="delete({{ $task->id }})" wire:confirm="{{ __('Confirm deletion?') }}" class="module-icon-button hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <x-ui.empty-state
                                        icon="task"
                                        :title="__('No tasks found.')"
                                        :message="__('Create the first task to keep follow-ups visible for the team.')"
                                    >
                                        <x-button.primary-link href="{{ route('tasks.create') }}" wire:navigate>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            {{ __('New Task') }}
                                        </x-button.primary-link>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile View: Cards -->
            <div class="module-mobile-list">
                @forelse($tasks as $task)
                    <div class="module-mobile-card">
                        <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-3 dark:border-gray-700/50">
                            <x-ui.status-chip :status="$task->status">{{ __($task->status) }}</x-ui.status-chip>
                            <span class="text-xs font-medium text-gray-500 flex items-center gap-1 dark:text-gray-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                {{ $task->due_date ? format_date($task->due_date) : __('No date') }}
                            </span>
                        </div>

                        <h3 class="font-bold text-gray-900 dark:text-white mb-2">{{ $task->title }}</h3>

                        <div class="flex items-center justify-between text-sm mb-4">
                            <div class="text-gray-500 dark:text-gray-400">
                                @if($task->relatable)
                                    <span class="text-xs">{{ __(class_basename($task->relatable_type)) }}: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $task->relatable->name ?? $task->relatable->subject ?? '#' . $task->relatable->id }}</span></span>
                                @else
                                    <span class="text-xs italic">{{ __('No linked record') }}</span>
                                @endif
                            </div>
                            <x-ui.status-chip :status="$task->priority">{{ __($task->priority) }}</x-ui.status-chip>
                        </div>

                        <div class="flex gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/50">
                            <a href="{{ route('tasks.edit', $task->id) }}" class="flex-1 text-center rounded-lg bg-amber-50 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400">{{ __('Edit') }}</a>
                            <button wire:click="delete({{ $task->id }})" wire:loading.attr="disabled" wire:target="delete({{ $task->id }})" wire:confirm="{{ __('Confirm deletion?') }}" class="flex-1 rounded-lg bg-rose-50 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-100 disabled:pointer-events-none disabled:opacity-50 dark:bg-rose-900/30 dark:text-rose-400"><x-ui.loading-label target="delete({{ $task->id }})" :label="__('Delete')" :loading="__('Deleting...')" /></button>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state
                        icon="task"
                        :title="__('No tasks found.')"
                        :message="__('Create the first task to keep follow-ups visible for the team.')"
                        size="compact"
                    />
                @endforelse
            </div>

            <div class="module-pagination">
                {{ $tasks->links() }}
            </div>
        </div>
    </div>
</div>
