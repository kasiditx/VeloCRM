<?php

declare(strict_types=1);

namespace App\Livewire\Tasks;

use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class TaskIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    protected $listeners = [
        'taskUpdated' => '$refresh',
    ];

    public function delete($id): void
    {
        $task = Task::findOrFail($id);
        $this->authorize('delete', $task);
        $task->delete();
        session()->flash('success', __('Task deleted successfully.'));

        $this->dispatch('taskUpdated');
    }

    public function render()
    {
        $this->authorize('viewAny', Task::class);

        return view('livewire.tasks.task-index', [
            'tasks' => Task::with(['user', 'relatable', 'assignee'])->latest()->paginate(10),
        ])->layout('layouts.app');
    }
}
