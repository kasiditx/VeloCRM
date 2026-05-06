<div class="form-page">
    <div class="work-container-narrow">
        <div class="work-header">
            <div>
                <p class="work-kicker">{{ __('Admin') }}</p>
                <h1 class="work-heading">{{ $userId ? __('Edit User') : __('Create User') }}</h1>
                <p class="work-subtitle">{{ __('Manage admin and staff accounts from one place.') }}</p>
            </div>
            <x-button.secondary-link href="{{ route('admin.users.index') }}" wire:navigate>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to users') }}
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
                    <label class="field-label">{{ __('Email') }}</label>
                    <input wire:model="email" type="email" class="field-control">
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Role') }}</label>
                    <select wire:model="role" class="field-control">
                        <option value="Admin">{{ __('Admin') }}</option>
                        <option value="Staff">{{ __('Staff') }}</option>
                    </select>
                    @error('role') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Status') }}</label>
                    <select wire:model="is_active" class="field-control">
                        <option value="1">{{ __('Active') }}</option>
                        <option value="0">{{ __('Inactive') }}</option>
                    </select>
                    @error('is_active') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Password') }}</label>
                    <input wire:model="password" type="password" class="field-control">
                    @if ($userId)
                        <p class="field-hint">{{ __('Leave blank to keep the current password.') }}</p>
                    @endif
                    @error('password') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Confirm Password') }}</label>
                    <input wire:model="password_confirmation" type="password" class="field-control">
                </div>
            </div>

            <div class="form-footer">
                <x-button.secondary-link href="{{ route('admin.users.index') }}" wire:navigate>{{ __('Cancel') }}</x-button.secondary-link>
                <x-button.primary type="submit" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $userId ? __('Update User') : __('Create User') }}</span>
                    <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                </x-button.primary>
            </div>
        </form>
    </div>
</div>
