<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Attachments') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Upload supporting files for this record.') }}</p>
        </div>
    </div>

    <form wire:submit="save" class="mt-5 space-y-3">
        <div>
            <input wire:model="file" type="file" class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-200 dark:text-gray-300 dark:file:bg-primary-900 dark:file:text-primary-200">
            @error('file') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div class="flex justify-end">
            <button type="submit" wire:loading.attr="disabled" wire:target="save,file" class="action-button action-button-primary">
                <x-ui.loading-label target="save,file" :label="__('Upload File')" :loading="__('Uploading...')" />
            </button>
        </div>
    </form>

    <div class="mt-6 space-y-3">
        @forelse ($attachments as $attachment)
            <div class="rounded-lg border border-gray-100 p-4 dark:border-gray-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <a href="{{ $attachment->url() }}" target="_blank" rel="noopener noreferrer" class="block truncate text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">
                            {{ $attachment->filename }}
                        </a>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            {{ number_format($attachment->size / 1024, 1) }} KB
                            · {{ $attachment->user?->name ?? __('Unknown user') }}
                            · {{ $attachment->created_at?->diffForHumans() }}
                        </p>
                    </div>
                    @if ($attachment->user_id === auth()->id() || auth()->user()->hasRole('Admin'))
                        <button wire:click="delete({{ $attachment->id }})" wire:confirm="{{ __('Delete this attachment?') }}" wire:loading.attr="disabled" wire:target="delete({{ $attachment->id }})" class="text-xs font-medium text-rose-600 transition hover:text-rose-700 disabled:pointer-events-none disabled:opacity-50 dark:text-rose-400">
                            <x-ui.loading-label target="delete({{ $attachment->id }})" :label="__('Delete')" :loading="__('Deleting...')" />
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state
                icon="file"
                :title="__('Keep the paperwork close.')"
                :message="__('Upload the first file so proposals, invoices, and approvals stay with this record.')"
                size="compact"
            />
        @endforelse
    </div>
</div>
