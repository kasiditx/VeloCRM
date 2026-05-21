<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use App\Livewire\Concerns\ManagesSavedFilterViews;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceIndex extends Component
{
    use AuthorizesRequests;
    use ManagesSavedFilterViews;
    use WithPagination;

    public string $search = '';

    public bool $showTrashed = false;

    public array $selectedIds = [];

    public string $bulkAction = '';

    public string $bulkStatus = 'Sent';

    public ?int $bulkUserId = null;

    public array $statuses = ['Draft', 'Sent', 'Partially Paid', 'Paid', 'Overdue', 'Cancelled'];

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
        session()->flash('success', 'Invoice moved to trash successfully.');
    }

    public function restore(int $id): void
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $invoice);
        $invoice->restore();
        session()->flash('success', 'Invoice restored successfully.');
    }

    public function forceDelete(int $id): void
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $invoice);
        $invoice->forceDelete();
        session()->flash('success', 'Invoice permanently deleted.');
    }

    public function updateStatus(int $id, string $status): void
    {
        if (! in_array($status, $this->statuses, true)) {
            return;
        }

        $invoice = Invoice::findOrFail($id);
        $this->authorize('update', $invoice);
        $invoice->update(['status' => $status]);
        session()->flash('success', __('Invoice status updated.'));
    }

    public function runBulkAction(): mixed
    {
        $ids = $this->selectedIds();

        if ($ids === []) {
            session()->flash('error', __('Select at least one record first.'));

            return null;
        }

        if ($this->bulkAction === 'export') {
            return $this->exportSelected($ids);
        }

        $invoices = Invoice::query()->whereKey($ids)->get();

        foreach ($invoices as $invoice) {
            match ($this->bulkAction) {
                'delete' => $this->bulkDelete($invoice),
                'status' => $this->bulkUpdateStatus($invoice),
                'assign' => $this->bulkAssign($invoice),
                default => null,
            };
        }

        $this->selectedIds = [];
        session()->flash('success', __('Bulk action completed.'));

        return null;
    }

    protected function filterViewResource(): string
    {
        return 'invoices';
    }

    protected function currentFilterViewState(): array
    {
        return [
            'search' => $this->search,
            'showTrashed' => $this->showTrashed,
        ];
    }

    protected function applyFilterViewState(array $filters): void
    {
        $this->search = (string) ($filters['search'] ?? '');
        $this->showTrashed = (bool) ($filters['showTrashed'] ?? false);
    }

    public function render()
    {
        return view('livewire.invoices.invoice-index', [
            'invoices' => Invoice::query()
                ->with('customer')
                ->when($this->showTrashed, fn ($q) => $q->onlyTrashed())
                ->when($this->search, fn ($q) => $q->where(function ($q): void {
                    $q->where('number', 'like', '%'.$this->search.'%')
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery
                            ->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('company', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%'));
                }))
                ->latest()
                ->paginate(10),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'savedFilterViews' => $this->savedFilterViews(),
        ]);
    }

    private function selectedIds(): array
    {
        return collect($this->selectedIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function bulkDelete(Invoice $invoice): void
    {
        $this->authorize('delete', $invoice);
        $invoice->delete();
    }

    private function bulkUpdateStatus(Invoice $invoice): void
    {
        if (! in_array($this->bulkStatus, $this->statuses, true)) {
            return;
        }

        $this->authorize('update', $invoice);
        $invoice->update(['status' => $this->bulkStatus]);
    }

    private function bulkAssign(Invoice $invoice): void
    {
        if ($this->bulkUserId === null) {
            return;
        }

        $this->authorize('update', $invoice);
        $invoice->forceFill(['user_id' => $this->bulkUserId])->save();
    }

    private function exportSelected(array $ids): StreamedResponse
    {
        return response()->streamDownload(function () use ($ids): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Number', 'Document Type', 'Customer', 'Status', 'Total', 'Balance Due', 'Currency']);

            Invoice::query()->with('customer')->whereKey($ids)->orderBy('number')->each(function (Invoice $invoice) use ($handle): void {
                $this->authorize('view', $invoice);
                fputcsv($handle, [
                    $invoice->number,
                    $invoice->documentTypeLabel(),
                    $invoice->customer?->name,
                    $invoice->status,
                    $invoice->total,
                    $invoice->balance_due,
                    $invoice->currency,
                ]);
            });

            fclose($handle);
        }, 'invoices-selected.csv');
    }
}
