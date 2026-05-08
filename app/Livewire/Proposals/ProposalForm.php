<?php

declare(strict_types=1);

namespace App\Livewire\Proposals;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Proposal;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Component;

class ProposalForm extends Component
{
    use AuthorizesRequests;

    public $proposalId;

    public $number;

    public $subject;

    public $content;

    public $total;

    public $status = 'Draft';

    public $customer_id;

    public $lead_id;

    protected $rules = [
        'number' => 'required',
        'subject' => 'required',
        'content' => 'required',
        'total' => 'required|numeric',
        'customer_id' => 'required_without:lead_id|nullable|exists:customers,id',
        'lead_id' => 'required_without:customer_id|nullable|exists:leads,id',
    ];

    public function mount($proposalId = null)
    {
        if ($proposalId) {
            $proposal = Proposal::findOrFail($proposalId);
            $this->authorize('update', $proposal);

            $this->proposalId = $proposal->id;
            $this->number = $proposal->number;
            $this->subject = $proposal->subject;
            $this->content = $proposal->content;
            $this->total = $proposal->total;
            $this->status = $proposal->status;
            $this->customer_id = $proposal->customer_id;
            $this->lead_id = $proposal->lead_id;
        } else {
            $this->authorize('create', Proposal::class);
            $this->number = 'PROP-'.strtoupper(Str::random(6));
        }
    }

    public function save()
    {
        $this->validate();

        $proposal = $this->proposalId ? Proposal::find($this->proposalId) : new Proposal;
        if ($proposal->exists) {
            $this->authorize('update', $proposal);
        } else {
            $this->authorize('create', Proposal::class);
        }

        $proposal->fill([
            'number' => $this->number,
            'subject' => $this->subject,
            'content' => $this->content,
            'total' => $this->total,
            'status' => $this->status,
            'customer_id' => $this->customer_id,
            'lead_id' => $this->lead_id,
        ]);

        if (! $proposal->exists) {
            $proposal->user_id = auth()->id();
        }

        $proposal->save();

        session()->flash('message', 'Proposal saved successfully.');

        return redirect()->route('proposals.index');
    }

    public function render()
    {
        return view('livewire.proposals.proposal-form', [
            'customers' => Customer::all(),
            'leads' => Lead::all(),
        ])->layout('layouts.app');
    }
}
