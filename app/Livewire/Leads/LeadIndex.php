<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Livewire\Concerns\ManagesSavedFilterViews;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadIndex extends Component
{
    use AuthorizesRequests;
    use ManagesSavedFilterViews;
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $sourceFilter = '';

    public bool $showTrashed = false;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public array $statuses = ['New', 'Contacted', 'Qualified', 'Lost', 'Won'];

    public array $sources = ['Website', 'Referral', 'Cold Call', 'Email', 'Social Media', 'Event', 'Other'];

    public array $selectedIds = [];

    public string $bulkAction = '';

    public string $bulkStatus = 'Contacted';

    public ?int $bulkUserId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sourceFilter' => ['except' => ''],
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

    public function updatingSourceFilter(): void
    {
        $this->resetPage();
    }

    public function updatingShowTrashed(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(int $id): void
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('delete', $lead);
        $lead->delete();
        session()->flash('success', 'Lead moved to trash successfully.');
    }

    public function restore(int $id): void
    {
        $lead = Lead::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $lead);
        $lead->restore();
        session()->flash('success', 'Lead restored successfully.');
    }

    public function forceDelete(int $id): void
    {
        $lead = Lead::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $lead);
        $lead->forceDelete();
        session()->flash('success', 'Lead permanently deleted.');
    }

    public function updateStatus(int $id, string $status): void
    {
        if (! in_array($status, $this->statuses, true)) {
            return;
        }

        $lead = Lead::findOrFail($id);
        $this->authorize('update', $lead);
        $lead->update(['status' => $status]);
        session()->flash('success', __('Lead status updated.'));
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

        $leads = Lead::query()->whereKey($ids)->get();

        foreach ($leads as $lead) {
            match ($this->bulkAction) {
                'delete' => $this->bulkDelete($lead),
                'status' => $this->bulkUpdateStatus($lead),
                'assign' => $this->bulkAssign($lead),
                default => null,
            };
        }

        $this->selectedIds = [];
        session()->flash('success', __('Bulk action completed.'));

        return null;
    }

    protected function filterViewResource(): string
    {
        return 'leads';
    }

    protected function currentFilterViewState(): array
    {
        return [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'sourceFilter' => $this->sourceFilter,
            'showTrashed' => $this->showTrashed,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ];
    }

    protected function applyFilterViewState(array $filters): void
    {
        $this->search = (string) ($filters['search'] ?? '');
        $this->statusFilter = (string) ($filters['statusFilter'] ?? '');
        $this->sourceFilter = (string) ($filters['sourceFilter'] ?? '');
        $this->showTrashed = (bool) ($filters['showTrashed'] ?? false);
        $this->sortField = in_array($filters['sortField'] ?? '', ['name', 'status', 'value', 'created_at'], true) ? $filters['sortField'] : 'created_at';
        $this->sortDirection = ($filters['sortDirection'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
    }

    public function render()
    {
        $this->authorize('viewAny', Lead::class);

        $query = Lead::query()
            ->with('customer')
            ->when($this->showTrashed, fn ($q) => $q->onlyTrashed())
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('company', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%');
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->sourceFilter, fn ($q) => $q->where('source', $this->sourceFilter))
            ->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.leads.lead-index', [
            'leads' => $query->paginate(15),
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

    private function bulkDelete(Lead $lead): void
    {
        $this->authorize('delete', $lead);
        $lead->delete();
    }

    private function bulkUpdateStatus(Lead $lead): void
    {
        if (! in_array($this->bulkStatus, $this->statuses, true)) {
            return;
        }

        $this->authorize('update', $lead);
        $lead->update(['status' => $this->bulkStatus]);
    }

    private function bulkAssign(Lead $lead): void
    {
        if ($this->bulkUserId === null) {
            return;
        }

        $this->authorize('update', $lead);
        $lead->forceFill(['user_id' => $this->bulkUserId])->save();
    }

    private function exportSelected(array $ids): StreamedResponse
    {
        return response()->streamDownload(function () use ($ids): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Phone', 'Company', 'Status', 'Source', 'Value']);

            Lead::query()->whereKey($ids)->orderBy('name')->each(function (Lead $lead) use ($handle): void {
                $this->authorize('view', $lead);
                fputcsv($handle, [$lead->name, $lead->email, $lead->phone, $lead->company, $lead->status, $lead->source, $lead->value]);
            });

            fclose($handle);
        }, 'leads-selected.csv');
    }
}
