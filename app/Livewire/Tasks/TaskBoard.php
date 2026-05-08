<?php

declare(strict_types=1);

namespace App\Livewire\Tasks;

use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TaskBoard extends Component
{
    use AuthorizesRequests;

    private const STATUSES = ['Todo', 'In Progress', 'Done', 'Cancelled'];

    protected $listeners = [
        'taskUpdated' => '$refresh',
    ];

    public function updateTaskStatus($taskId, $newStatus): void
    {
        validator(
            ['taskId' => $taskId, 'newStatus' => $newStatus],
            [
                'taskId' => ['required', 'integer', 'exists:tasks,id'],
                'newStatus' => ['required', Rule::in(self::STATUSES)],
            ],
        )->validate();

        $task = Task::findOrFail($taskId);
        $this->authorize('update', $task);

        if ($task->status === $newStatus) {
            return;
        }

        $task->update(['status' => $newStatus]);

        session()->flash('success', __('Task moved to :status.', ['status' => __($newStatus)]));

        $this->dispatch('taskUpdated');
    }

    public function render()
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Task::with(['relatable', 'assignee'])
            ->orderBy('due_date', 'asc')
            ->get()
            ->groupBy('status');

        return view('livewire.tasks.task-board', [
            'tasks' => $tasks,
            'statuses' => self::STATUSES,
        ])->layout('layouts.app');
    }
}
