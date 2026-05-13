@props(['fields' => []])

@if(count($fields) > 0)
    <div class="border-t border-gray-200 pt-6 dark:border-gray-800">
        <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Custom Fields') }}</h3>
        <div class="form-grid">
            @foreach($fields as $field)
                <div class="{{ $field['type'] === 'textarea' ? 'md:col-span-2' : '' }}" wire:key="custom-field-{{ $field['id'] }}">
                    <label class="field-label">{{ $field['label'] }}</label>

                    @if($field['type'] === 'textarea')
                        <textarea wire:model="customFieldValues.{{ $field['id'] }}" rows="4" class="field-control"></textarea>
                    @elseif($field['type'] === 'number')
                        <input wire:model="customFieldValues.{{ $field['id'] }}" type="number" step="0.01" class="field-control">
                    @elseif($field['type'] === 'date')
                        <input wire:model="customFieldValues.{{ $field['id'] }}" type="date" class="field-control">
                    @elseif($field['type'] === 'select')
                        <select wire:model="customFieldValues.{{ $field['id'] }}" class="field-control">
                            <option value="">{{ __('Select option') }}</option>
                            @foreach($field['options'] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif($field['type'] === 'boolean')
                        <label class="mt-2 flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                            <input wire:model="customFieldValues.{{ $field['id'] }}" type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-950">
                            <span>{{ __('Yes') }}</span>
                        </label>
                    @else
                        <input wire:model="customFieldValues.{{ $field['id'] }}" type="text" class="field-control">
                    @endif

                    @error('customFieldValues.'.$field['id']) <p class="field-error">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>
    </div>
@endif
