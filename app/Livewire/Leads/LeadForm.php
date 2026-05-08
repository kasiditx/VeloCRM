<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class LeadForm extends Component
{
    use AuthorizesRequests;

    public ?int $leadId = null;

    public ?Lead $lead = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $company = '';

    public string $status = 'New';

    public string $source = '';

    public string $value = '0';

    public string $notes = '';

    public ?int $assigned_to = null;

    public array $statuses = ['New', 'Contacted', 'Qualified', 'Lost', 'Won'];

    public array $sources = ['Website', 'Referral', 'Cold Call', 'Email', 'Social Media', 'Event', 'Other'];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'status' => 'required|in:New,Contacted,Qualified,Lost,Won',
            'source' => 'nullable|string|max:100',
            'value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }

    public function mount(?int $leadId = null): void
    {
        $this->leadId = $leadId;

        if ($leadId) {
            $this->lead = Lead::findOrFail($leadId);
            $this->authorize('update', $this->lead);

            $this->name = $this->lead->name;
            $this->email = $this->lead->email ?? '';
            $this->phone = $this->lead->phone ?? '';
            $this->company = $this->lead->company ?? '';
            $this->status = $this->lead->status;
            $this->source = $this->lead->source ?? '';
            $this->value = (string) $this->lead->value;
            $this->notes = $this->lead->notes ?? '';
            $this->assigned_to = $this->lead->user_id;
        } else {
            $this->authorize('create', Lead::class);
            $this->assigned_to = auth()->id();
        }
    }

    public function save(): void
    {
        $data = $this->validate();

        $data['value'] = (float) $data['value'];
        $ownerId = auth()->user()->hasRole('Admin')
            ? ($data['assigned_to'] ?? auth()->id())
            : auth()->id();
        unset($data['assigned_to']);

        if ($this->leadId) {
            $this->authorize('update', $this->lead);
            $this->lead->user_id = $ownerId;
            $this->lead->update($data);
            session()->flash('success', 'Lead updated successfully.');
            $this->redirect(route('leads.show', $this->leadId), navigate: true);
        } else {
            $this->authorize('create', Lead::class);
            $lead = new Lead($data);
            $lead->user_id = $ownerId;
            $lead->save();
            session()->flash('success', 'Lead created successfully.');
            $this->redirect(route('leads.show', $lead->id), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.leads.lead-form', [
            'users' => User::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
