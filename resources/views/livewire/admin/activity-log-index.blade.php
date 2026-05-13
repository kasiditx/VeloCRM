<div class="work-page">
    <div class="work-container">
        <div class="work-header">
            <div>
                <p class="work-kicker">{{ __('Admin') }}</p>
                <h1 class="work-heading">{{ __('Activity Log') }}</h1>
                <p class="work-subtitle">{{ __('Review recorded changes across CRM records, filtered by user, model, or date range.') }}</p>
            </div>
        </div>

        <div class="module-panel p-5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div>
                    <label class="field-label">{{ __('User') }}</label>
                    <select wire:model.live="userId" class="field-control">
                        <option value="">{{ __('All users') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">{{ __('Model') }}</label>
                    <select wire:model.live="modelType" class="field-control">
                        <option value="">{{ __('All models') }}</option>
                        @foreach($modelTypes as $class => $label)
                            <option value="{{ $class }}">{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">{{ __('Start Date') }}</label>
                    <input wire:model.live="startDate" type="date" class="field-control">
                </div>
                <div>
                    <label class="field-label">{{ __('End Date') }}</label>
                    <input wire:model.live="endDate" type="date" class="field-control">
                </div>
                <div class="flex items-end">
                    <button type="button" wire:click="clearFilters" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                        {{ __('Clear Filters') }}
                    </button>
                </div>
            </div>
        </div>

        <section class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-3 border-b border-gray-100 p-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Recorded Activity') }}</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ __('Latest matching events from Spatie Activitylog.') }}</p>
                </div>
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    {{ $activities->total() }} {{ __('records') }}
                </span>
            </div>

            <x-activity.timeline :activities="$activities" />

            @if($activities->hasPages())
                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                    {{ $activities->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
