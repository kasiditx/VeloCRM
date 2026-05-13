<?php

namespace App\Livewire\Proposals;

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

    public string $publicShareUrl = '';

    public function mount(int $proposalId)
    {
        $this->proposal = Proposal::with(['customer', 'lead'])->findOrFail($proposalId);
        $this->authorize('view', $this->proposal);

        $this->companyName = Setting::get('company_name', velocrm_company_name());
        $this->companyAddress = Setting::get('company_address', '');
        $this->publicShareUrl = $this->proposal->publicShareUrl();
    }

    public function render()
    {
        return view('livewire.proposals.proposal-show')
            ->title(__('Proposal :subject', ['subject' => $this->proposal->subject]));
    }
}
