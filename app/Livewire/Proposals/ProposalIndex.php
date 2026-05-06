<?php

declare(strict_types=1);

namespace App\Livewire\Proposals;

use App\Models\Proposal;
use Livewire\Component;
use Livewire\WithPagination;

class ProposalIndex extends Component
{
    use WithPagination;

    public bool $showTrashed = false;

    protected $queryString = [
        'showTrashed' => ['except' => false],
    ];

    public function updatingShowTrashed(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $proposal = Proposal::findOrFail($id);
        $proposal->delete();
        session()->flash('message', 'Proposal moved to trash successfully.');
    }

    public function restore(int $id): void
    {
        $proposal = Proposal::onlyTrashed()->findOrFail($id);
        $proposal->restore();
        session()->flash('message', 'Proposal restored successfully.');
    }

    public function forceDelete(int $id): void
    {
        $proposal = Proposal::onlyTrashed()->findOrFail($id);
        $proposal->forceDelete();
        session()->flash('message', 'Proposal permanently deleted.');
    }

    public function render()
    {
        return view('livewire.proposals.proposal-index', [
            'proposals' => Proposal::query()
                ->with(['customer', 'lead'])
                ->when($this->showTrashed, fn ($q) => $q->onlyTrashed())
                ->latest()
                ->paginate(10),
        ])->layout('layouts.app');
    }
}
