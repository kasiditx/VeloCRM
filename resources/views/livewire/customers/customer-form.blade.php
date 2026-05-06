<div class="form-page">
    <div class="work-container-narrow">
        <div class="work-header">
            <div>
                <p class="work-kicker">{{ __('Customers') }}</p>
                <h1 class="work-heading">{{ $customerId ? __('Edit Customer') : __('New Customer') }}</h1>
                <p class="work-subtitle">{{ __('Store core account information and optionally preserve the original lead relationship.') }}</p>
            </div>
            <x-button.secondary-link href="{{ route('customers.index') }}" wire:navigate>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to customers') }}
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
                <div class="md:col-span-2">
                    <label class="field-label">{{ __('Linked Lead') }}</label>
                    <select wire:model="lead_id" class="field-control">
                        <option value="">{{ __('No linked lead') }}</option>
                        @foreach ($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->name }}{{ $lead->company ? ' - ' . $lead->company : '' }}</option>
                        @endforeach
                    </select>
                    @error('lead_id') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="field-label">{{ __('Address') }}</label>
                    <textarea wire:model="address" rows="5" class="field-control"></textarea>
                    @error('address') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-footer">
                <x-button.secondary-link href="{{ route('customers.index') }}" wire:navigate>
                    {{ __('Cancel') }}
                </x-button.secondary-link>
                <x-button.primary type="submit" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $customerId ? __('Update Customer') : __('Create Customer') }}</span>
                    <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                </x-button.primary>
            </div>
        </form>
    </div>
</div>
