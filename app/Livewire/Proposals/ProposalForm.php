<?php

declare(strict_types=1);

namespace App\Livewire\Proposals;

use App\Models\Proposal;
use App\Models\Customer;
use App\Models\Lead;
use Livewire\Component;
use Illuminate\Support\Str;

class ProposalForm extends Component
{
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
            $this->proposalId = $proposal->id;
            $this->number = $proposal->number;
            $this->subject = $proposal->subject;
            $this->content = $proposal->content;
            $this->total = $proposal->total;
            $this->status = $proposal->status;
            $this->customer_id = $proposal->customer_id;
            $this->lead_id = $proposal->lead_id;
        } else {
            $this->number = 'PROP-' . strtoupper(Str::random(6));
        }
    }

    public function save()
    {
        $this->validate();

        $proposal = $this->proposalId ? Proposal::find($this->proposalId) : new Proposal();
        $proposal->fill([
            'number' => $this->number,
            'subject' => $this->subject,
            'content' => $this->content,
            'total' => $this->total,
            'status' => $this->status,
            'customer_id' => $this->customer_id,
            'lead_id' => $this->lead_id,
            'user_id' => auth()->id(),
        ]);
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
