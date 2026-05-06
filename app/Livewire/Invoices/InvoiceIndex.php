<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';
    public bool $showTrashed = false;

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

    public function delete(int $id): void
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorize('delete', $invoice);
        $invoice->delete();
        session()->flash('message', 'Invoice moved to trash successfully.');
    }

    public function restore(int $id): void
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $invoice);
        $invoice->restore();
        session()->flash('message', 'Invoice restored successfully.');
    }

    public function forceDelete(int $id): void
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $invoice);
        $invoice->forceDelete();
        session()->flash('message', 'Invoice permanently deleted.');
    }

    public function render()
    {
        return view('livewire.invoices.invoice-index', [
            'invoices' => Invoice::query()
                ->with('customer')
                ->when($this->showTrashed, fn ($q) => $q->onlyTrashed())
                ->when($this->search, fn ($q) => $q->where(function ($q): void {
                    $q->where('number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery
                            ->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('company', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%'));
                }))
                ->latest()
                ->paginate(10),
        ]);
    }
}
