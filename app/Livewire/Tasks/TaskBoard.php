<?php

declare(strict_types=1);

namespace App\Livewire\Tasks;

use App\Models\Task;
use Livewire\Component;

class TaskBoard extends Component
{
    public function mount()
    {
    }

    public function updateTaskStatus($taskId, $newStatus)
    {
        $task = Task::findOrFail($taskId);
        $task->status = $newStatus;
        $task->save();
    }

    public function render()
    {
        $tasks = Task::with(['relatable', 'assignee'])
            ->orderBy('due_date', 'asc')
            ->get()
            ->groupBy('status');

        return view('livewire.tasks.task-board', [
            'tasks' => $tasks,
        ])->layout('layouts.app');
    }
}
