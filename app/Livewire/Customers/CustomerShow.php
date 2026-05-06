<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;

class CustomerShow extends Component
{
    public Customer $customer;

    public function mount(int $customerId): void
    {
        $this->customer = Customer::with(['lead', 'user', 'invoices', 'proposals'])->findOrFail($customerId);
    }

    public function delete(): void
    {
        $this->customer->delete();
        session()->flash('success', 'Customer deleted.');
        $this->redirect(route('customers.index'), navigate: true);
    }

    public function render()
    {
        $activities = \Spatie\Activitylog\Models\Activity::where('subject_type', Customer::class)
            ->where('subject_id', $this->customer->id)
            ->latest()
            ->take(20)
            ->get();

        $tasks = \App\Models\Task::where('relatable_type', Customer::class)
            ->where('relatable_id', $this->customer->id)
            ->orderBy('due_date')
            ->get();

        return view('livewire.customers.customer-show', [
            'activities' => $activities,
            'tasks'      => $tasks,
        ])->layout('layouts.app');
    }
}
