<?php

declare(strict_types=1);

namespace App\Livewire\Proposals;

use App\Livewire\Concerns\ManagesSavedFilterViews;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProposalIndex extends Component
{
    use AuthorizesRequests;
    use ManagesSavedFilterViews;
    use WithPagination;

    public bool $showTrashed = false;

    public string $search = '';

    public string $statusFilter = '';

    public array $selectedIds = [];

    public string $bulkAction = '';

    public string $bulkStatus = 'Sent';

    public ?int $bulkUserId = null;

    public array $statuses = ['Draft', 'Sent', 'Open', 'Revised', 'Declined', 'Accepted'];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'showTrashed' => ['except' => false],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingShowTrashed(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $proposal = Proposal::findOrFail($id);
        $this->authorize('delete', $proposal);
        $proposal->delete();
        session()->flash('success', 'Proposal moved to trash successfully.');
    }

    public function restore(int $id): void
    {
        $proposal = Proposal::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $proposal);
        $proposal->restore();
        session()->flash('success', 'Proposal restored successfully.');
    }

    public function forceDelete(int $id): void
    {
        $proposal = Proposal::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $proposal);
        $proposal->forceDelete();
        session()->flash('success', 'Proposal permanently deleted.');
    }

    public function updateStatus(int $id, string $status): void
    {
        if (! in_array($status, $this->statuses, true)) {
            return;
        }

        $proposal = Proposal::findOrFail($id);
        $this->authorize('update', $proposal);
        $proposal->update(['status' => $status]);
        session()->flash('success', __('Proposal status updated.'));
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

        Proposal::query()->whereKey($ids)->get()->each(function (Proposal $proposal): void {
            match ($this->bulkAction) {
                'delete' => $this->bulkDelete($proposal),
                'status' => $this->bulkUpdateStatus($proposal),
                'assign' => $this->bulkAssign($proposal),
                default => null,
            };
        });

        $this->selectedIds = [];
        session()->flash('success', __('Bulk action completed.'));

        return null;
    }

    protected function filterViewResource(): string
    {
        return 'proposals';
    }

    protected function currentFilterViewState(): array
    {
        return [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'showTrashed' => $this->showTrashed,
        ];
    }

    protected function applyFilterViewState(array $filters): void
    {
        $this->search = (string) ($filters['search'] ?? '');
        $this->statusFilter = (string) ($filters['statusFilter'] ?? '');
        $this->showTrashed = (bool) ($filters['showTrashed'] ?? false);
    }

    public function render()
    {
        $this->authorize('viewAny', Proposal::class);

        return view('livewire.proposals.proposal-index', [
            'proposals' => Proposal::query()
                ->with(['customer', 'lead'])
                ->when($this->showTrashed, fn ($q) => $q->onlyTrashed())
                ->when($this->search, fn ($q) => $q->where(function ($q): void {
                    $q->where('number', 'like', '%'.$this->search.'%')
                        ->orWhere('subject', 'like', '%'.$this->search.'%');
                }))
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->latest()
                ->paginate(10),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'savedFilterViews' => $this->savedFilterViews(),
        ])->layout('layouts.app');
    }

    private function selectedIds(): array
    {
        return collect($this->selectedIds)->map(fn (mixed $id): int => (int) $id)->filter()->unique()->values()->all();
    }

    private function bulkDelete(Proposal $proposal): void
    {
        $this->authorize('delete', $proposal);
        $proposal->delete();
    }

    private function bulkUpdateStatus(Proposal $proposal): void
    {
        if (! in_array($this->bulkStatus, $this->statuses, true)) {
            return;
        }

        $this->authorize('update', $proposal);
        $proposal->update(['status' => $this->bulkStatus]);
    }

    private function bulkAssign(Proposal $proposal): void
    {
        if ($this->bulkUserId === null) {
            return;
        }

        $this->authorize('update', $proposal);
        $proposal->forceFill(['user_id' => $this->bulkUserId])->save();
    }

    private function exportSelected(array $ids): StreamedResponse
    {
        return response()->streamDownload(function () use ($ids): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Number', 'Subject', 'Customer', 'Status', 'Total']);

            Proposal::query()->with('customer')->whereKey($ids)->orderBy('number')->each(function (Proposal $proposal) use ($handle): void {
                $this->authorize('view', $proposal);
                fputcsv($handle, [$proposal->number, $proposal->subject, $proposal->customer?->name, $proposal->status, $proposal->total]);
            });

            fclose($handle);
        }, 'proposals-selected.csv');
    }
}
