<?php

declare(strict_types=1);

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Models\Customer;
use App\Models\Lead;
use App\Notifications\TaskAssignedNotification;
use App\Support\SafeNotifier;
use Livewire\Component;

class TaskForm extends Component
{
    public $taskId;
    public $title;
    public $description;
    public $status = 'Todo';
    public $priority = 'Medium';
    public $due_date;
    public $relatable_type;
    public $relatable_id;
    public $assigned_to;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|in:Todo,In Progress,Done,Cancelled',
        'priority' => 'required|in:Low,Medium,High,Urgent',
        'due_date' => 'nullable|date',
        'assigned_to' => 'nullable|exists:users,id',
    ];

    public function mount($taskId = null, $relatableType = null, $relatableId = null): void
    {
        if ($taskId) {
            $task = Task::findOrFail($taskId);
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
            $this->due_date = now()->addDay()->format('Y-m-d');
            $this->relatable_type = $relatableType;
            $this->relatable_id = $relatableId;
        }
    }

    public function save()
    {
        $this->validate();

        $existingAssignedTo = null;
        $task = $this->taskId ? Task::find($this->taskId) : new Task();
        if ($task->exists) {
            $existingAssignedTo = $task->assigned_to;
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
            'user_id' => auth()->id(),
        ]);
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

        session()->flash('message', 'Task saved successfully.');
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
