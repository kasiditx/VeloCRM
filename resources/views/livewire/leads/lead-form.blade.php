<div class="form-page">
    <div class="work-container-narrow">
        <div class="work-header">
            <div>
                <p class="work-kicker">{{ __('Leads') }}</p>
                <h1 class="work-heading">{{ $leadId ? __('Edit Lead') : __('New Lead') }}</h1>
                <p class="work-subtitle">{{ __('Capture qualification details so sales and operations can act on the same record.') }}</p>
            </div>
            <x-button.secondary-link href="{{ route('leads.index') }}" wire:navigate>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to leads') }}
            </x-button.secondary-link>
        </div>

        <form wire:submit="save" class="form-panel">
            <div class="form-grid">
                <div>
                    <label class="field-label">{{ __('Name') }}</label>
                    <input wire:model="name" type="text" class="field-control">
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Company') }}</label>
                    <input wire:model="company" type="text" class="field-control">
                    @error('company') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Email') }}</label>
                    <input wire:model="email" type="email" class="field-control">
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Phone') }}</label>
                    <input wire:model="phone" type="text" class="field-control">
                    @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Status') }}</label>
                    <select wire:model="status" class="field-control">
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ __($statusOption) }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Source') }}</label>
                    <select wire:model="source" class="field-control">
                        <option value="">{{ __('Select source') }}</option>
                        @foreach ($sources as $sourceOption)
                            <option value="{{ $sourceOption }}">{{ __($sourceOption) }}</option>
                        @endforeach
                    </select>
                    @error('source') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Estimated Value') }}</label>
                    <input wire:model="value" type="number" min="0" step="0.01" class="field-control">
                    @error('value') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Owner') }}</label>
                    <select wire:model="assigned_to" class="field-control">
                        <option value="">{{ __('Use current user') }}</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('assigned_to') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="field-label">{{ __('Internal Notes') }}</label>
                <textarea wire:model="notes" rows="5" class="field-control"></textarea>
                @error('notes') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-footer">
                <x-button.secondary-link href="{{ route('leads.index') }}" wire:navigate>{{ __('Cancel') }}</x-button.secondary-link>
                <x-button.primary type="submit" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $leadId ? __('Update Lead') : __('Create Lead') }}</span>
                    <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                </x-button.primary>
            </div>
        </form>
    </div>
</div>
