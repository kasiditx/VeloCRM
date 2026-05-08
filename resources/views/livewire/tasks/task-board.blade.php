<div class="py-10">
    <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Kanban Board') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Track task progress visually across each workflow stage.') }}</p>
            </div>
            <div class="flex gap-3">
                <x-button.secondary-link href="{{ route('tasks.index') }}">
                    {{ __('Task List') }}
                </x-button.secondary-link>
                <x-button.primary-link href="{{ route('tasks.create') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('New Task') }}
                </x-button.primary-link>
            </div>
        </div>

        <div class="flex overflow-x-auto pb-4 gap-4 snap-x snap-mandatory">
            @foreach ($statuses as $status)
                @php($taskGroup = $tasks->get($status, collect()))
                <div
                    class="snap-start flex-shrink-0 w-72 sm:w-80 rounded-2xl bg-gray-100 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 p-4 shadow-sm"
                    ondragover="event.preventDefault()"
                    ondrop="window.Livewire.find('{{ $_instance->getId() }}').updateTaskStatus(event.dataTransfer.getData('task-id'), '{{ $status }}')"
                >
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">{{ __($status) }}</h2>
                        <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full shadow-sm border border-gray-200 dark:border-gray-600">
                            {{ $taskGroup->count() }}
                        </span>
                    </div>

                    <div class="space-y-3 min-h-[120px]">
                        @if($taskGroup->isNotEmpty())
                            @foreach ($taskGroup as $task)
                                <div
                                    class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 cursor-grab active:cursor-grabbing transition hover:shadow-md"
                                    draggable="true"
                                    ondragstart="event.dataTransfer.setData('task-id', {{ $task->id }})"
                                >
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight break-words">{{ $task->title }}</h4>
                                        <x-ui.status-chip :status="$task->priority" class="shrink-0 text-[10px]">
                                            {{ __($task->priority) }}
                                        </x-ui.status-chip>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                        {{ $task->due_date ? format_date($task->due_date) : __('No date') }}
                                    </p>

                                    @if ($task->relatable)
                                        <p class="mb-3 text-[10px] uppercase font-semibold text-gray-400 dark:text-gray-500">
                                            {{ __(class_basename($task->relatable_type)) }}:
                                            <span class="text-gray-600 dark:text-gray-400 font-normal ml-1">{{ $task->relatable->name ?? $task->relatable->subject ?? ('#' . $task->relatable->id) }}</span>
                                        </p>
                                    @endif

                                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            @if($task->assignee)
                                                <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-primary-500 to-purple-500 flex items-center justify-center text-[10px] text-white font-bold border-2 border-white dark:border-gray-900 shadow-sm" title="{{ $task->assignee->name }}">
                                                    {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                                </div>
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $task->assignee->name }}</span>
                                            @else
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('Unassigned') }}</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('tasks.edit', $task->id) }}" class="text-[10px] font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 hover:underline">
                                            {{ __('Edit') }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl text-center h-full min-h-[120px]">
                                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                                <a href="{{ route('tasks.create', ['status' => $status]) }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">{{ __('New Task') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
