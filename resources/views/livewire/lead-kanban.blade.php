<div class="py-10">
    <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Lead Pipeline') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Drag and drop leads between stages.') }}</p>
            </div>
            <a href="{{ route('export.leads') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 shadow-sm self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M16 9l-4-4m0 0L8 9m4-4v12"/></svg>
                {{ __('Export XLSX') }}
            </a>
        </div>

        {{-- Kanban Board --}}
        <div class="flex overflow-x-auto pb-4 gap-4 snap-x snap-mandatory" wire:sortable-group="updateTaskOrder">
            @foreach($statuses as $status)
                <div class="snap-start flex-shrink-0 w-72 sm:w-80 rounded-2xl bg-gray-100 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 p-4 shadow-sm"
                     wire:sortable-group.item-group="{{ $status }}" wire:key="group-{{ $status }}">

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">{{ __($status) }}</h3>
                        <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full shadow-sm border border-gray-200 dark:border-gray-600">
                            {{ isset($leads[$status]) ? $leads[$status]->count() : 0 }}
                        </span>
                    </div>

                    <div class="space-y-3 min-h-[120px]">
                        @if(isset($leads[$status]) && $leads[$status]->isNotEmpty())
                            @foreach($leads[$status] as $lead)
                                <div class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 cursor-grab active:cursor-grabbing transition hover:shadow-md"
                                     wire:sortable-group.item="{{ $lead->id }}" wire:key="lead-{{ $lead->id }}">
                                    <div class="flex justify-between items-start mb-2 gap-2" wire:sortable-group.item-handle>
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight break-words">{{ $lead->name }}</h4>
                                        <span class="shrink-0 text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900 px-2 py-0.5 rounded-full">
                                            {{ format_currency($lead->value, 0) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 truncate">{{ $lead->company ?? __('No company') }}</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $lead->updated_at->diffForHumans() }}</span>
                                        <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-primary-500 to-purple-500 flex items-center justify-center text-[10px] text-white font-bold border-2 border-white dark:border-gray-900 shadow-sm">
                                            {{ strtoupper(substr($lead->name, 0, 1)) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl text-center h-full min-h-[120px]">
                                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                                <a href="{{ route('leads.create') }}" wire:navigate class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">{{ __('Create Lead') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
