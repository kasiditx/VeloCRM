<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Notes') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Shared context for this record.') }}</p>
        </div>
    </div>

    <form wire:submit="save" class="mt-5 space-y-3">
        <div>
            <textarea wire:model="content" rows="4" placeholder="{{ __('Add a note for your team...') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"></textarea>
            @error('content') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div class="flex justify-end">
            <button type="submit" wire:loading.attr="disabled" wire:target="save" class="action-button action-button-primary">
                <x-ui.loading-label target="save" :label="__('Add Note')" :loading="__('Adding...')" />
            </button>
        </div>
    </form>

    <div class="mt-6 space-y-3">
        @forelse ($notes as $note)
            <div class="rounded-lg border border-gray-100 p-4 dark:border-gray-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-200">{{ $note->content }}</p>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            {{ $note->user?->name ?? __('Unknown user') }} · {{ $note->created_at?->diffForHumans() }}
                        </p>
                    </div>
                    @if ($note->user_id === auth()->id() || auth()->user()->hasRole('Admin'))
                        <button wire:click="delete({{ $note->id }})" wire:confirm="{{ __('Delete this note?') }}" wire:loading.attr="disabled" wire:target="delete({{ $note->id }})" class="text-xs font-medium text-rose-600 transition hover:text-rose-700 disabled:pointer-events-none disabled:opacity-50 dark:text-rose-400">
                            <x-ui.loading-label target="delete({{ $note->id }})" :label="__('Delete')" :loading="__('Deleting...')" />
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state
                icon="note"
                :title="__('Start the internal thread.')"
                :message="__('Add the first note so the next teammate sees the context immediately.')"
                size="compact"
            />
        @endforelse
    </div>
</div>
