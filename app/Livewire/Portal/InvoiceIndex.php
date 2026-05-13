<?php

declare(strict_types=1);

namespace App\Livewire\Portal;

use App\Models\Invoice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $customerId = Auth::user()->customer_id;

        return view('livewire.portal.invoice-index', [
            'invoices' => Invoice::query()
                ->withoutGlobalScopes()
                ->with('customer')
                ->where('customer_id', $customerId)
                ->when($this->search, fn ($query) => $query->where('number', 'like', '%'.$this->search.'%'))
                ->latest()
                ->paginate(10),
        ])->layout('layouts.portal');
    }
}
