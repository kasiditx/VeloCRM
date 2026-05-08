<div class="detail-page">
    <div class="work-container">
        <div class="work-header">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="work-heading">{{ $lead->name }}</h1>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ match($lead->status) { 'Won' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300', 'Lost' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300', 'Qualified' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300', 'Contacted' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300', default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' } }}">
                        {{ __($lead->status) }}
                    </span>
                </div>
                <p class="work-subtitle">{{ $lead->company ?: __('No company assigned') }}</p>
            </div>

            <div class="work-actions">
                @if (! $lead->customer)
                    <button wire:click="openConvertModal" wire:loading.attr="disabled" wire:target="openConvertModal" class="inline-flex items-center gap-1.5 justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:pointer-events-none disabled:opacity-55 disabled:saturate-50">
                        <x-ui.loading-label target="openConvertModal" :label="__('Convert to Customer')" :loading="__('Opening...')" />
                    </button>
                @endif
                <x-button.secondary-link href="{{ route('leads.edit', $lead->id) }}" wire:navigate>{{ __('Edit') }}</x-button.secondary-link>
                <x-button.danger wire:click="delete" data-velo-confirm="{{ __('Delete this lead?') }}" wire:loading.attr="disabled" wire:target="delete">
                    <x-ui.loading-label target="delete" :label="__('Delete')" :loading="__('Deleting...')" />
                </x-button.danger>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-6">
                <div class="detail-card">
                    <h2 class="detail-card-title">{{ __('Lead Overview') }}</h2>
                    <dl class="detail-grid">
                        <div>
                            <dt class="detail-label">{{ __('Email') }}</dt>
                            <dd class="detail-value">{{ $lead->email ?: __('Not provided') }}</dd>
                        </div>
                        <div>
                            <dt class="detail-label">{{ __('Phone') }}</dt>
                            <dd class="detail-value">
                                {{ $lead->phone ?: __('Not provided') }}
                                @if($lead->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="ml-2 inline-flex items-center gap-1 rounded-md bg-[#25D366] px-2 py-1 text-xs font-semibold text-white hover:bg-[#128C7E] transition-colors">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 21.031c-1.636 0-3.238-.431-4.664-1.251l-.334-.194-3.468.91 1.05-3.328-.21-.336c-.846-1.341-1.29-2.883-1.29-4.47 0-4.62 3.76-8.38 8.385-8.38 2.238 0 4.341.872 5.924 2.456C18.966 8.005 19.82 10.088 19.82 12.332c0 4.62-3.76 8.38-8.38 8.38m8.349-15.086C18.15 3.714 15.163 2.5 12.03 2.5c-5.467 0-9.917 4.449-9.92 9.916 0 1.748.456 3.454 1.324 4.954l-1.42 4.5 4.606-1.209c1.455.79 3.09 1.205 4.773 1.206 5.467 0 9.917-4.448 9.92-9.916 0-2.65-.87-5.143-2.747-7.01h-.001zM17.472 14.86c-.297-.15-.176-.874-.176-.874l-2.028-.016s-.19-.514-.265-.968c-.149-.894 1.054-.925 1.054-.925l-.264-.99c-.433-.092-2.164.394-2.746.884-.339.284-.6.82-.676 1.134-.148.618-.465 3.25-.465 3.25s-1.026.066-1.574.004c-.389-.044-.817-.183-1.042-.455-.268-.328-.352-1.011-.271-1.632.062-.486.21-1.05.35-1.554.124-.442.24-1.082-.01-1.492-.352-.581-2.001-1.127-2.793-1.077-1.109.068-1.52.887-1.789 1.605-.447 1.196.22 3.66 1.761 5.305.815.867 1.956 1.584 3.02 1.838 1.143.272 2.533.155 3.662-.128 1.056-.264 2.21-.902 2.894-1.879.622-.887.82-1.921.36-2.072z"/></svg>
                                        {{ __('Chat') }}
                                    </a>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="detail-label">{{ __('Source') }}</dt>
                            <dd class="detail-value">{{ $lead->source ? __($lead->source) : __('Unspecified') }}</dd>
                        </div>
                        <div>
                            <dt class="detail-label">{{ __('Estimated Value') }}</dt>
                            <dd class="detail-value font-semibold">{{ format_currency($lead->value) }}</dd>
                        </div>
                        <div>
                            <dt class="detail-label">{{ __('Owner') }}</dt>
                            <dd class="detail-value">{{ $lead->user?->name ?? __('Unassigned') }}</dd>
                        </div>
                        <div>
                            <dt class="detail-label">{{ __('Created') }}</dt>
                            <dd class="detail-value">{{ format_date($lead->created_at, velocrm_date_format() . ' H:i') }}</dd>
                        </div>
                    </dl>
                    <div class="detail-note">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Internal Notes') }}</h3>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300">{{ $lead->notes ?: __('No internal notes yet.') }}</p>
                    </div>
                    @if ($lead->customer)
                        <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300">
                            {{ __('Converted customer:') }}
                            <a href="{{ route('customers.show', $lead->customer->id) }}" wire:navigate class="font-semibold underline">{{ $lead->customer->name }}</a>
                        </div>
                    @endif
                </div>

                <div class="detail-card">
                    <div class="flex items-center justify-between">
                        <h2 class="detail-card-title">{{ __('Related Tasks') }}</h2>
                        <a href="{{ route('tasks.create', ['relatableType' => \App\Models\Lead::class, 'relatableId' => $lead->id]) }}" wire:navigate class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">{{ __('New task') }}</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($tasks as $task)
                            <div class="detail-list-item">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $task->title }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $task->due_date ? format_date($task->due_date) : __('No due date') }}</p>
                                    </div>
                                    <x-ui.status-chip :status="$task->status">{{ __($task->status) }}</x-ui.status-chip>
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state
                                icon="task"
                                :title="__('No tasks linked to this lead yet.')"
                                :message="__('Create a follow-up task so this lead does not go quiet.')"
                                size="compact"
                            >
                                <x-button.primary-link href="{{ route('tasks.create', ['relatableType' => \App\Models\Lead::class, 'relatableId' => $lead->id]) }}" wire:navigate>{{ __('New task') }}</x-button.primary-link>
                            </x-ui.empty-state>
                        @endforelse
                    </div>
                </div>

                <livewire:notes.notes-list
                    :notable-type="\App\Models\Lead::class"
                    :notable-id="$lead->id"
                    :key="'lead-notes-' . $lead->id"
                />
            </div>

            <div class="space-y-6">
                <div class="detail-card">
                    <h2 class="detail-card-title">{{ __('Activity Timeline') }}</h2>
                    <div class="mt-4 space-y-4">
                        @forelse ($activities as $activity)
                            <div class="timeline-item">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $activity->description ?: __('Record updated') }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $activity->created_at?->diffForHumans() }}</p>
                            </div>
                        @empty
                            <x-ui.empty-state
                                icon="report"
                                :title="__('No activity recorded yet.')"
                                :message="__('New notes, tasks, conversion, and status changes will appear in this timeline.')"
                                size="compact"
                            />
                        @endforelse
                    </div>
                </div>

                <livewire:attachments.attachment-panel
                    :attachable-type="\App\Models\Lead::class"
                    :attachable-id="$lead->id"
                    :key="'lead-attachments-' . $lead->id"
                />
            </div>
        </div>
    </div>

    @if ($showConvertModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 px-4" role="dialog" aria-modal="true" aria-labelledby="convert-lead-title" aria-describedby="convert-lead-description">
            <div class="w-full max-w-2xl rounded-xl bg-white p-6 shadow-2xl outline-none dark:bg-gray-900" tabindex="-1">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="convert-lead-title" class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Convert Lead to Customer') }}</h2>
                        <p id="convert-lead-description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Review the details below before creating the customer record.') }}</p>
                    </div>
                    <button type="button" aria-label="{{ __('Close') }}" wire:click="$set('showConvertModal', false)" wire:loading.attr="disabled" wire:target="convertToCustomer" class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-gray-800 dark:hover:text-gray-200">✕</button>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Customer Name') }}</label>
                        <input wire:model="customerName" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        @error('customerName') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Company') }}</label>
                        <input wire:model="customerCompany" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        @error('customerCompany') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Email') }}</label>
                        <input wire:model="customerEmail" type="email" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        @error('customerEmail') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Phone') }}</label>
                        <input wire:model="customerPhone" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        @error('customerPhone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Address') }}</label>
                        <textarea wire:model="customerAddress" rows="4" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"></textarea>
                        @error('customerAddress') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showConvertModal', false)" wire:loading.attr="disabled" wire:target="convertToCustomer" class="action-button action-button-secondary">{{ __('Cancel') }}</button>
                    <button wire:click="convertToCustomer" wire:loading.attr="disabled" wire:target="convertToCustomer" class="action-button action-button-success">
                        <x-ui.loading-label target="convertToCustomer" :label="__('Create Customer')" :loading="__('Converting...')" />
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
