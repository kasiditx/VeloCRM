<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Models\Lead;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class LeadIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $sourceFilter = '';

    public bool $showTrashed = false;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public array $statuses = ['New', 'Contacted', 'Qualified', 'Lost', 'Won'];

    public array $sources = ['Website', 'Referral', 'Cold Call', 'Email', 'Social Media', 'Event', 'Other'];

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
        ])->layout('layouts.app');
    }
}
