<div class="work-page">
    <div class="work-container">
        <div class="work-header">
            <div>
                <p class="work-kicker">{{ __('Admin') }}</p>
                <h1 class="work-heading">{{ __('Custom Fields') }}</h1>
                <p class="work-subtitle">{{ __('Add extra fields to lead, customer, and invoice forms without changing the core database columns.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[24rem_minmax(0,1fr)]">
            <form wire:submit="save" class="form-panel self-start">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $editingId ? __('Edit Custom Field') : __('New Custom Field') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Select fields need options, one per line or comma-separated.') }}</p>
                </div>

                <div>
                    <label class="field-label">{{ __('Model') }}</label>
                    <select wire:model="model_type" class="field-control">
                        @foreach($modelTypes as $class => $label)
                            <option value="{{ $class }}">{{ __($label) }}</option>
                        @endforeach
                    </select>
                    @error('model_type') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">{{ __('Key') }}</label>
                    <input wire:model="key" type="text" class="field-control" placeholder="customer_segment">
                    @error('key') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">{{ __('English Label') }}</label>
                    <input wire:model="label_en" type="text" class="field-control">
                    @error('label_en') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">{{ __('Thai Label') }}</label>
                    <input wire:model="label_th" type="text" class="field-control">
                    @error('label_th') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">{{ __('Type') }}</label>
                    <select wire:model.live="type" class="field-control">
                        @foreach($fieldTypes as $value => $label)
                            <option value="{{ $value }}">{{ __($label) }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                @if($type === \App\Models\CustomField::TYPE_SELECT)
                    <div>
                        <label class="field-label">{{ __('Options') }}</label>
                        <textarea wire:model="options_text" rows="5" class="field-control" placeholder="Enterprise&#10;SME&#10;Government"></textarea>
                        @error('options_text') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="form-footer">
                    @if($editingId)
                        <x-button.secondary type="button" wire:click="resetForm">{{ __('Cancel') }}</x-button.secondary>
                    @endif
                    <x-button.primary type="submit" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('Save Custom Field') }}</span>
                        <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                    </x-button.primary>
                </div>
            </form>

            <div class="module-panel p-6">
                <div class="space-y-6">
                    @forelse($modelTypes as $class => $label)
                        <section>
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __($label) }}</h2>
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    {{ ($customFields[$class] ?? collect())->count() }}
                                </span>
                            </div>

                            <div class="space-y-3">
                                @forelse($customFields[$class] ?? [] as $field)
                                    <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/60 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $field->label_en }}</h3>
                                                <span class="rounded-full bg-primary-50 px-2 py-0.5 text-[11px] font-bold uppercase text-primary-700 dark:bg-primary-950 dark:text-primary-300">{{ $field->type }}</span>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                <code>{{ $field->key }}</code>
                                                @if($field->label_th)
                                                    · {{ $field->label_th }}
                                                @endif
                                            </p>
                                            @if($field->type === \App\Models\CustomField::TYPE_SELECT)
                                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ implode(', ', $field->normalizedOptions()) }}</p>
                                            @endif
                                        </div>
                                        <div class="flex shrink-0 gap-2">
                                            <button type="button" wire:click="edit({{ $field->id }})" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                                                {{ __('Edit') }}
                                            </button>
                                            <button type="button" wire:click="delete({{ $field->id }})" wire:confirm="{{ __('Confirm deletion?') }}" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                                                {{ __('Delete') }}
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <x-ui.empty-state
                                        icon="file"
                                        :title="__('No custom fields found.')"
                                        :message="__('Add a field to capture extra details on this model without changing core screens.')"
                                        size="compact"
                                    />
                                @endforelse
                            </div>
                        </section>
                    @empty
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
