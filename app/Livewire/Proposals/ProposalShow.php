<?php

namespace App\Livewire\Proposals;

use App\Models\Proposal;
use App\Models\Setting;
use Livewire\Component;

class ProposalShow extends Component
{
    public Proposal $proposal;
    public string $companyName;
    public string $companyAddress;

    public function mount(int $proposalId)
    {
        $this->proposal = Proposal::with(['customer', 'lead', 'items.taxTemplate'])->findOrFail($proposalId);

        $this->companyName = Setting::get('company_name', velocrm_company_name());
        $this->companyAddress = Setting::get('company_address', '');
    }

    public function render()
    {
        return view('livewire.proposals.proposal-show')
            ->title(__('Proposal :subject', ['subject' => $this->proposal->subject]));
    }
}
