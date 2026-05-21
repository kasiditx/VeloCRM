<?php

declare(strict_types=1);

namespace App\Livewire\Tasks;

use App\Livewire\Concerns\ManagesSavedFilterViews;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskIndex extends Component
{
    use AuthorizesRequests;
    use ManagesSavedFilterViews;
    use WithPagination;

    protected $listeners = [
        'taskUpdated' => '$refresh',
    ];

    public array $selectedIds = [];

    public string $bulkAction = '';

    public string $bulkStatus = 'In Progress';

    public ?int $bulkUserId = null;

    public array $statuses = ['Todo', 'In Progress', 'Done', 'Cancelled'];

    public string $search = '';

    public string $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete($id): void
    {
        $task = Task::findOrFail($id);
        $this->authorize('delete', $task);
        $task->delete();
        session()->flash('success', __('Task deleted successfully.'));

        $this->dispatch('taskUpdated');
    }

    public function updateStatus(int $id, string $status): void
    {
        if (! in_array($status, $this->statuses, true)) {
            return;
        }

        $task = Task::findOrFail($id);
        $this->authorize('update', $task);
        $task->update(['status' => $status]);
        session()->flash('success', __('Task status updated.'));
        $this->dispatch('taskUpdated');
    }

    public function runBulkAction(): mixed
    {
        $ids = $this->selectedIds();

        if ($ids === []) {
            session()->flash('error', __('Select at least one record first.'));

            return null;
        }

        if ($this->bulkAction === 'export') {
            return $this->exportSelected($ids);
        }

        $tasks = Task::query()->whereKey($ids)->get();

        foreach ($tasks as $task) {
            match ($this->bulkAction) {
                'delete' => $this->bulkDelete($task),
                'status' => $this->bulkUpdateStatus($task),
                'assign' => $this->bulkAssign($task),
                default => null,
            };
        }

        $this->selectedIds = [];
        session()->flash('success', __('Bulk action completed.'));
        $this->dispatch('taskUpdated');

        return null;
    }

    protected function filterViewResource(): string
    {
        return 'tasks';
    }

    protected function currentFilterViewState(): array
    {
        return [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
        ];
    }

    protected function applyFilterViewState(array $filters): void
    {
        $this->search = (string) ($filters['search'] ?? '');
        $this->statusFilter = (string) ($filters['statusFilter'] ?? '');
    }

    public function render()
    {
        $this->authorize('viewAny', Task::class);

        return view('livewire.tasks.task-index', [
            'tasks' => Task::with(['user', 'relatable', 'assignee'])
                ->when($this->search, fn ($query) => $query->where(function ($query): void {
                    $query->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                }))
                ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
                ->latest()
                ->paginate(10),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'savedFilterViews' => $this->savedFilterViews(),
        ])->layout('layouts.app');
    }

    private function selectedIds(): array
    {
        return collect($this->selectedIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function bulkDelete(Task $task): void
    {
        $this->authorize('delete', $task);
        $task->delete();
    }

    private function bulkUpdateStatus(Task $task): void
    {
        if (! in_array($this->bulkStatus, $this->statuses, true)) {
            return;
        }

        $this->authorize('update', $task);
        $task->update(['status' => $this->bulkStatus]);
    }

    private function bulkAssign(Task $task): void
    {
        if ($this->bulkUserId === null) {
            return;
        }

        $this->authorize('update', $task);
        $task->update(['assigned_to' => $this->bulkUserId]);
    }

    private function exportSelected(array $ids): StreamedResponse
    {
        return response()->streamDownload(function () use ($ids): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Title', 'Status', 'Priority', 'Due Date', 'Assignee']);

            Task::query()->with('assignee')->whereKey($ids)->orderBy('title')->each(function (Task $task) use ($handle): void {
                $this->authorize('view', $task);
                fputcsv($handle, [$task->title, $task->status, $task->priority, $task->due_date, $task->assignee?->name]);
            });

            fclose($handle);
        }, 'tasks-selected.csv');
    }
}
