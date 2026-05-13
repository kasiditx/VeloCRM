<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class CustomerShow extends Component
{
    use AuthorizesRequests;

    public Customer $customer;

    public string $activeTab = 'overview';

    public function mount(int $customerId): void
    {
        $this->customer = Customer::with(['lead', 'user', 'invoices', 'proposals'])->findOrFail($customerId);
        $this->authorize('view', $this->customer);
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->customer);

        $this->customer->delete();
        session()->flash('success', 'Customer deleted.');
        $this->redirect(route('customers.index'), navigate: true);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['overview', 'activity'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function render()
    {
        $activities = Activity::where('subject_type', Customer::class)
            ->where('subject_id', $this->customer->id)
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();

        $tasks = Task::where('relatable_type', Customer::class)
            ->where('relatable_id', $this->customer->id)
            ->orderBy('due_date')
            ->get();

        return view('livewire.customers.customer-show', [
            'activities' => $activities,
            'tasks' => $tasks,
        ])->layout('layouts.app');
    }
}
