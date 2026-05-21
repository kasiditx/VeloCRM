<div class="form-page">
    <div class="work-container-narrow">
        <div class="work-header">
            <div>
                <p class="work-kicker">{{ __('Proposals') }}</p>
                <h1 class="work-heading">{{ $proposalId ? __('Edit Proposal') : __('New Proposal') }}</h1>
                <p class="work-subtitle">{{ __('Draft proposals for prospects and customers with full content and pricing.') }}</p>
            </div>
            <x-button.secondary-link href="{{ route('proposals.index') }}" wire:navigate>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to proposals') }}
            </x-button.secondary-link>
        </div>

        <form wire:submit="save" class="form-panel" data-draft-key="velocrm.proposal-form.{{ $proposalId ?: 'new' }}">
            <div class="form-grid">
                <div class="md:col-span-2">
                    <label class="field-label">{{ __('Subject') }}</label>
                    <input wire:model="subject" type="text" class="field-control">
                    @error('subject') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Related Customer (Optional)') }}</label>
                    <select wire:model="customer_id" class="field-control">
                        <option value="">{{ __('Select Customer') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">{{ __('Related Lead (Optional)') }}</label>
                    <select wire:model="lead_id" class="field-control">
                        <option value="">{{ __('Select Lead') }}</option>
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">{{ __('Total Amount') }}</label>
                    <input wire:model="total" type="number" min="0" step="0.01" class="field-control">
                    @error('total') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">{{ __('Status') }}</label>
                    <select wire:model="status" class="field-control">
                        <option value="Draft">{{ __('Draft') }}</option>
                        <option value="Sent">{{ __('Sent') }}</option>
                        <option value="Open">{{ __('Open') }}</option>
                        <option value="Revised">{{ __('Revised') }}</option>
                        <option value="Declined">{{ __('Declined') }}</option>
                        <option value="Accepted">{{ __('Accepted') }}</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="field-label">{{ __('Content') }}</label>
                <textarea wire:model="content" rows="10" class="field-control" placeholder="{{ __('Write your proposal details here...') }}"></textarea>
                @error('content') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-footer">
                <x-button.secondary-link href="{{ route('proposals.index') }}" wire:navigate>{{ __('Cancel') }}</x-button.secondary-link>
                <x-button.primary type="submit" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $proposalId ? __('Update Proposal') : __('Create Proposal') }}</span>
                    <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                </x-button.primary>
            </div>
        </form>
    </div>
</div>
