<div class="detail-page">
    <div class="work-container">
        <div class="work-header">
            <div>
                <p class="work-kicker">{{ __('Customers') }}</p>
                <h1 class="work-heading">{{ $customer->name }}</h1>
                <p class="work-subtitle">{{ $customer->company ?: __('No company assigned') }}</p>
            </div>

            <div class="work-actions">
                <x-button.secondary-link href="{{ route('customers.edit', $customer->id) }}" wire:navigate>{{ __('Edit') }}</x-button.secondary-link>
                <x-button.danger wire:click="delete" wire:confirm="{{ __('Delete this customer?') }}">{{ __('Delete') }}</x-button.danger>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-6">
                <div class="detail-card">
                    <h2 class="detail-card-title">{{ __('Customer Overview') }}</h2>
                    <dl class="detail-grid">
                        <div>
                            <dt class="detail-label">{{ __('Email') }}</dt>
                            <dd class="detail-value">{{ $customer->email ?: __('Not provided') }}</dd>
                        </div>
                        <div>
                            <dt class="detail-label">{{ __('Phone') }}</dt>
                            <dd class="detail-value">
                                {{ $customer->phone ?: __('Not provided') }}
                                @if($customer->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank" class="ml-2 inline-flex items-center gap-1 rounded-md bg-[#25D366] px-2 py-1 text-xs font-semibold text-white hover:bg-[#128C7E] transition-colors">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 21.031c-1.636 0-3.238-.431-4.664-1.251l-.334-.194-3.468.91 1.05-3.328-.21-.336c-.846-1.341-1.29-2.883-1.29-4.47 0-4.62 3.76-8.38 8.385-8.38 2.238 0 4.341.872 5.924 2.456C18.966 8.005 19.82 10.088 19.82 12.332c0 4.62-3.76 8.38-8.38 8.38m8.349-15.086C18.15 3.714 15.163 2.5 12.03 2.5c-5.467 0-9.917 4.449-9.92 9.916 0 1.748.456 3.454 1.324 4.954l-1.42 4.5 4.606-1.209c1.455.79 3.09 1.205 4.773 1.206 5.467 0 9.917-4.448 9.92-9.916 0-2.65-.87-5.143-2.747-7.01h-.001zM17.472 14.86c-.297-.15-.176-.874-.176-.874l-2.028-.016s-.19-.514-.265-.968c-.149-.894 1.054-.925 1.054-.925l-.264-.99c-.433-.092-2.164.394-2.746.884-.339.284-.6.82-.676 1.134-.148.618-.465 3.25-.465 3.25s-1.026.066-1.574.004c-.389-.044-.817-.183-1.042-.455-.268-.328-.352-1.011-.271-1.632.062-.486.21-1.05.35-1.554.124-.442.24-1.082-.01-1.492-.352-.581-2.001-1.127-2.793-1.077-1.109.068-1.52.887-1.789 1.605-.447 1.196.22 3.66 1.761 5.305.815.867 1.956 1.584 3.02 1.838 1.143.272 2.533.155 3.662-.128 1.056-.264 2.21-.902 2.894-1.879.622-.887.82-1.921.36-2.072z"/></svg>
                                        {{ __('Chat') }}
                                    </a>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="detail-label">{{ __('Owner') }}</dt>
                            <dd class="detail-value">{{ $customer->user?->name ?? __('Unassigned') }}</dd>
                        </div>
                        <div>
                            <dt class="detail-label">{{ __('Created') }}</dt>
                            <dd class="detail-value">{{ format_date($customer->created_at, velocrm_date_format() . ' H:i') }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="detail-label">{{ __('Address') }}</dt>
                            <dd class="detail-value whitespace-pre-line">{{ $customer->address ?: __('No address on file.') }}</dd>
                        </div>
                    </dl>
                    @if ($customer->lead)
                        <div class="mt-5 rounded-lg bg-sky-50 p-4 text-sm text-sky-800 dark:bg-sky-950/30 dark:text-sky-300">
                            {{ __('Origin lead:') }}
                            <a href="{{ route('leads.show', $customer->lead->id) }}" wire:navigate class="font-semibold underline">{{ $customer->lead->name }}</a>
                        </div>
                    @endif
                </div>

                <div class="detail-card">
                    <h2 class="detail-card-title">{{ __('Related Invoices') }}</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($customer->invoices as $invoice)
                            <div class="detail-list-item flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $invoice->number }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ format_date($invoice->invoice_date) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ format_currency($invoice->total) }}</p>
                                    <x-ui.status-chip :status="$invoice->status">{{ __($invoice->status) }}</x-ui.status-chip>
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state
                                icon="invoice"
                                :title="__('No invoices linked to this customer yet.')"
                                :message="__('Create an invoice from this customer when billing starts.')"
                                size="compact"
                            >
                                <x-button.primary-link href="{{ route('invoices.create') }}" wire:navigate>{{ __('New Invoice') }}</x-button.primary-link>
                            </x-ui.empty-state>
                        @endforelse
                    </div>
                </div>

                <div class="detail-card">
                    <h2 class="detail-card-title">{{ __('Related Proposals') }}</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($customer->proposals as $proposal)
                            <div class="detail-list-item flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $proposal->number }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $proposal->subject }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ format_currency($proposal->total) }}</p>
                                    <x-ui.status-chip :status="$proposal->status">{{ __($proposal->status) }}</x-ui.status-chip>
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state
                                icon="proposal"
                                :title="__('No proposals linked to this customer yet.')"
                                :message="__('Create a proposal so pricing and scope stay attached to this account.')"
                                size="compact"
                            >
                                <x-button.primary-link href="{{ route('proposals.create') }}" wire:navigate>{{ __('New Proposal') }}</x-button.primary-link>
                            </x-ui.empty-state>
                        @endforelse
                    </div>
                </div>

                <livewire:notes.notes-list
                    :notable-type="\App\Models\Customer::class"
                    :notable-id="$customer->id"
                    :key="'customer-notes-' . $customer->id"
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
                                :message="__('New notes, tasks, invoices, and proposal changes will appear in this timeline.')"
                                size="compact"
                            />
                        @endforelse
                    </div>
                </div>

                <div class="detail-card">
                    <h2 class="detail-card-title">{{ __('Related Tasks') }}</h2>
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
                                :title="__('No tasks linked to this customer yet.')"
                                :message="__('Create a follow-up task so the next action is clear.')"
                                size="compact"
                            >
                                <x-button.primary-link href="{{ route('tasks.create', ['relatableType' => \App\Models\Customer::class, 'relatableId' => $customer->id]) }}" wire:navigate>{{ __('New Task') }}</x-button.primary-link>
                            </x-ui.empty-state>
                        @endforelse
                    </div>
                </div>

                <livewire:attachments.attachment-panel
                    :attachable-type="\App\Models\Customer::class"
                    :attachable-id="$customer->id"
                    :key="'customer-attachments-' . $customer->id"
                />
            </div>
        </div>
    </div>
</div>
