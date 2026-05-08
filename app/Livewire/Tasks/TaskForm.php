<?php

declare(strict_types=1);

namespace App\Livewire\Tasks;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Support\SafeNotifier;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TaskForm extends Component
{
    use AuthorizesRequests;

    private const STATUSES = ['Todo', 'In Progress', 'Done', 'Cancelled'];

    public $taskId;

    public $title;

    public $description;

    public $status = 'Todo';

    public $priority = 'Medium';

    public $due_date;

    public $relatable_type;

    public $relatable_id;

    public $assigned_to;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(self::STATUSES)],
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }

    public function mount($taskId = null, $relatableType = null, $relatableId = null): void
    {
        if ($taskId) {
            $task = Task::findOrFail($taskId);
            $this->authorize('update', $task);

            $this->taskId = $task->id;
            $this->title = $task->title;
            $this->description = $task->description;
            $this->status = $task->status;
            $this->priority = $task->priority;
            $this->due_date = $task->due_date ? date('Y-m-d', strtotime($task->due_date)) : null;
            $this->relatable_type = $task->relatable_type;
            $this->relatable_id = $task->relatable_id;
            $this->assigned_to = $task->assigned_to;
        } else {
            $this->authorize('create', Task::class);

            $this->due_date = now()->addDay()->format('Y-m-d');
            $this->relatable_type = $relatableType;
            $this->relatable_id = $relatableId;

            $status = request()->query('status');
            if (is_string($status) && in_array($status, self::STATUSES, true)) {
                $this->status = $status;
            }
        }
    }

    public function save()
    {
        $this->validate();

        $existingAssignedTo = null;
        $task = $this->taskId ? Task::find($this->taskId) : new Task;
        if ($task->exists) {
            $this->authorize('update', $task);
            $existingAssignedTo = $task->assigned_to;
        } else {
            $this->authorize('create', Task::class);
        }

        $task->fill([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date,
            'relatable_type' => $this->relatable_type,
            'relatable_id' => $this->relatable_id,
            'assigned_to' => $this->assigned_to,
        ]);
        if (! $task->exists) {
            $task->user_id = auth()->id();
        }
        $task->save();

        $task->load('assignee');

        if (
            $task->assigned_to &&
            $task->assigned_to !== $existingAssignedTo &&
            $task->assignee &&
            $task->assignee->email &&
            $task->assignee->is_active &&
            $task->assigned_to !== auth()->id()
        ) {
            SafeNotifier::send($task->assignee, new TaskAssignedNotification($task), [
                'task_id' => $task->id,
                'assigned_to' => $task->assigned_to,
            ]);
        }

        session()->flash('success', __('Task saved successfully.'));

        $this->dispatch('taskUpdated');

        return redirect()->route('tasks.board');
    }

    public function render()
    {
        return view('livewire.tasks.task-form', [
            'users' => User::all(),
            'customers' => Customer::all(),
            'leads' => Lead::all(),
        ])->layout('layouts.app');
    }
}
