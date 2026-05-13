<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-primary-600 dark:text-primary-300">{{ __('Customer Portal') }}</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 dark:text-white">{{ __('Profile') }}</h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('Update your account and billing contact details.') }}</p>
    </div>

    <form wire:submit="save" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="field-label">{{ __('Login Name') }}</label>
                <input wire:model="name" type="text" class="field-control">
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="field-label">{{ __('Login Email') }}</label>
                <input wire:model="email" type="email" class="field-control">
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="field-label">{{ __('Billing Name') }}</label>
                <input wire:model="customerName" type="text" class="field-control">
                @error('customerName') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="field-label">{{ __('Phone') }}</label>
                <input wire:model="phone" type="text" class="field-control">
                @error('phone') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="field-label">{{ __('Company') }}</label>
                <input wire:model="company" type="text" class="field-control">
                @error('company') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="field-label">{{ __('Billing Address') }}</label>
                <textarea wire:model="address" rows="4" class="field-control"></textarea>
                @error('address') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-8 border-t border-slate-100 pt-6 dark:border-slate-800">
            <h2 class="text-lg font-black text-slate-950 dark:text-white">{{ __('Change Password') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Leave these fields blank if you do not want to change your password.') }}</p>
            <div class="mt-5 grid gap-5 sm:grid-cols-3">
                <div>
                    <label class="field-label">{{ __('Current Password') }}</label>
                    <input wire:model="current_password" type="password" class="field-control">
                    @error('current_password') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('New Password') }}</label>
                    <input wire:model="password" type="password" class="field-control">
                    @error('password') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Confirm Password') }}</label>
                    <input wire:model="password_confirmation" type="password" class="field-control">
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <x-button.primary type="submit" wire:loading.attr="disabled" wire:target="save">
                <x-ui.loading-label target="save" :label="__('Save Profile')" :loading="__('Saving...')" />
            </x-button.primary>
        </div>
    </form>
</div>
