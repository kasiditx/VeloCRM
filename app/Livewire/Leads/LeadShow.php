<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class LeadShow extends Component
{
    use AuthorizesRequests;

    public Lead $lead;

    public bool $showConvertModal = false;

    public string $activeTab = 'overview';

    // Convert-to-customer fields
    public string $customerName = '';

    public string $customerEmail = '';

    public string $customerPhone = '';

    public string $customerCompany = '';

    public string $customerAddress = '';

    public function mount(int $leadId): void
    {
        $this->lead = Lead::with(['user', 'customer'])->findOrFail($leadId);
        $this->authorize('view', $this->lead);
    }

    public function openConvertModal(): void
    {
        $this->authorize('update', $this->lead);
        $this->authorize('create', Customer::class);

        $this->customerName = $this->lead->name;
        $this->customerEmail = $this->lead->email ?? '';
        $this->customerPhone = $this->lead->phone ?? '';
        $this->customerCompany = $this->lead->company ?? '';
        $this->showConvertModal = true;
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['overview', 'activity'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function convertToCustomer(): void
    {
        $this->authorize('update', $this->lead);
        $this->authorize('create', Customer::class);

        if ($this->lead->customer) {
            $this->redirect(route('customers.show', $this->lead->customer->id), navigate: true);

            return;
        }

        $this->validate([
            'customerName' => 'required|string|max:255',
            'customerEmail' => 'nullable|email|max:255',
            'customerPhone' => 'nullable|string|max:50',
            'customerCompany' => 'nullable|string|max:255',
            'customerAddress' => 'nullable|string',
        ]);

        $customer = new Customer([
            'lead_id' => $this->lead->id,
            'name' => $this->customerName,
            'email' => $this->customerEmail,
            'phone' => $this->customerPhone,
            'company' => $this->customerCompany,
            'address' => $this->customerAddress,
        ]);
        $customer->user_id = $this->lead->user_id ?? auth()->id();
        $customer->save();

        $this->lead->update(['status' => 'Won']);
        $this->showConvertModal = false;

        session()->flash('success', 'Lead successfully converted to customer!');
        $this->redirect(route('customers.show', $customer->id), navigate: true);
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->lead);

        $this->lead->delete();
        session()->flash('success', 'Lead deleted.');
        $this->redirect(route('leads.index'), navigate: true);
    }

    public function render()
    {
        $activities = Activity::where('subject_type', Lead::class)
            ->where('subject_id', $this->lead->id)
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();

        $tasks = Task::where('relatable_type', Lead::class)
            ->where('relatable_id', $this->lead->id)
            ->orderBy('due_date')
            ->get();

        return view('livewire.leads.lead-show', [
            'activities' => $activities,
            'tasks' => $tasks,
        ])->layout('layouts.app');
    }
}
