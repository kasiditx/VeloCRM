<?php

declare(strict_types=1);

namespace App\Livewire\PublicShare;

use App\Models\Proposal;
use App\Models\Setting;
use Livewire\Component;

class ProposalShow extends Component
{
    public Proposal $proposal;

    public string $companyName = '';

    public string $companyAddress = '';

    public function mount(string $token): void
    {
        $this->proposal = Proposal::withoutGlobalScopes()
            ->with(['customer', 'lead'])
            ->where('public_token', $token)
            ->firstOrFail();

        $this->proposal->markPublicView((string) request()->ip());

        $this->companyName = (string) Setting::get('company_name', velocrm_company_name());
        $this->companyAddress = (string) Setting::get('company_address', '');
    }

    public function accept(): void
    {
        if ($this->proposal->status !== 'Accepted') {
            $this->proposal->forceFill(['status' => 'Accepted'])->save();
            session()->flash('success', __('Proposal accepted successfully.'));
        }
    }

    public function reject(): void
    {
        if ($this->proposal->status !== 'Declined') {
            $this->proposal->forceFill(['status' => 'Declined'])->save();
            session()->flash('success', __('Proposal declined successfully.'));
        }
    }

    public function render()
    {
        return view('livewire.public-share.proposal-show')
            ->layout('layouts.public-share')
            ->title(__('Proposal :subject', ['subject' => $this->proposal->subject]));
    }
}
