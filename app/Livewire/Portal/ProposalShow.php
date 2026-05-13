<?php

declare(strict_types=1);

namespace App\Livewire\Portal;

use App\Models\Proposal;
use App\Models\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ProposalShow extends Component
{
    use AuthorizesRequests;

    public Proposal $proposal;

    public string $companyName;

    public string $companyAddress;

    public function mount(int $proposalId): void
    {
        $this->proposal = Proposal::withoutGlobalScopes()
            ->with(['customer', 'lead'])
            ->findOrFail($proposalId);
        $this->authorize('view', $this->proposal);

        $this->companyName = Setting::get('company_name', velocrm_company_name());
        $this->companyAddress = Setting::get('company_address', '');
    }

    public function accept(): void
    {
        $this->authorize('respond', $this->proposal);

        if ($this->proposal->status !== 'Accepted') {
            $this->proposal->status = 'Accepted';
            $this->proposal->save();
            session()->flash('success', __('Proposal accepted successfully.'));
        }
    }

    public function reject(): void
    {
        $this->authorize('respond', $this->proposal);

        if ($this->proposal->status !== 'Declined') {
            $this->proposal->status = 'Declined';
            $this->proposal->save();
            session()->flash('success', __('Proposal declined successfully.'));
        }
    }

    public function render()
    {
        return view('livewire.portal.proposal-show')
            ->layout('layouts.portal')
            ->title(__('Proposal :subject', ['subject' => $this->proposal->subject]));
    }
}
