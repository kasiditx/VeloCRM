<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showTrashed = false;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'showTrashed' => ['except' => false],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingShowTrashed(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        session()->flash('success', 'Customer moved to trash successfully.');
    }

    public function restore(int $id): void
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();
        session()->flash('success', 'Customer restored successfully.');
    }

    public function forceDelete(int $id): void
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->forceDelete();
        session()->flash('success', 'Customer permanently deleted.');
    }

    public function render()
    {
        $query = Customer::query()
            ->when($this->showTrashed, fn ($q) => $q->onlyTrashed())
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('company', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            }))
            ->withCount(['invoices', 'proposals'])
            ->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.customers.customer-index', [
            'customers' => $query->paginate(15),
        ])->layout('layouts.app');
    }
}
