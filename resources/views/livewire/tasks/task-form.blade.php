<div class="form-page">
    <div class="mx-auto max-w-2xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="work-header">
            <div>
                <p class="work-kicker">{{ __('Tasks') }}</p>
                <h1 class="work-heading">{{ $taskId ?? null ? __('Edit Task') : __('New Task') }}</h1>
                <p class="work-subtitle">{{ __('Manage your workflow and keep track of important tasks.') }}</p>
            </div>
            <x-button.secondary-link href="{{ route('tasks.board') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Cancel') }}
            </x-button.secondary-link>
        </div>

        <form wire:submit.prevent="save" class="form-panel">
            <div>
                <label for="title" class="field-label">{{ __('Task Title') }}</label>
                <input type="text" wire:model="title" id="title"
                    class="field-control">
                @error('title') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="description" class="field-label">{{ __('Description') }}</label>
                <textarea wire:model="description" id="description" rows="4"
                    class="field-control"></textarea>
                @error('description') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="status" class="field-label">{{ __('Status') }}</label>
                    <select wire:model="status" id="status"
                        class="field-control">
                        <option value="Todo">{{ __('Todo') }}</option>
                        <option value="In Progress">{{ __('In Progress') }}</option>
                        <option value="Done">{{ __('Done') }}</option>
                        <option value="Cancelled">{{ __('Cancelled') }}</option>
                    </select>
                </div>
                <div>
                    <label for="priority" class="field-label">{{ __('Priority') }}</label>
                    <select wire:model="priority" id="priority"
                        class="field-control">
                        <option value="Low">{{ __('Low') }}</option>
                        <option value="Medium">{{ __('Medium') }}</option>
                        <option value="High">{{ __('High') }}</option>
                        <option value="Urgent">{{ __('Urgent') }}</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="due_date" class="field-label">{{ __('Due Date') }}</label>
                    <input type="date" wire:model="due_date" id="due_date"
                        class="field-control">
                </div>
                <div>
                    <label for="assigned_to" class="field-label">{{ __('Assigned To') }}</label>
                    <select wire:model="assigned_to" id="assigned_to"
                        class="field-control">
                        <option value="">{{ __('Unassigned') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-footer">
                <x-button.secondary-link href="{{ route('tasks.board') }}">{{ __('Cancel') }}</x-button.secondary-link>
                <x-button.primary type="submit" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ __('Save Task') }}</span>
                    <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                </x-button.primary>
            </div>
        </form>
    </div>
</div>
