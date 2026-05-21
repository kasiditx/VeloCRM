<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Livewire\Concerns\ManagesSavedFilterViews;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerIndex extends Component
{
    use AuthorizesRequests;
    use ManagesSavedFilterViews;
    use WithPagination;

    public string $search = '';

    public bool $showTrashed = false;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public array $selectedIds = [];

    public string $bulkAction = '';

    public ?int $bulkUserId = null;

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
        $this->authorize('delete', $customer);
        $customer->delete();
        session()->flash('success', 'Customer moved to trash successfully.');
    }

    public function restore(int $id): void
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $customer);
        $customer->restore();
        session()->flash('success', 'Customer restored successfully.');
    }

    public function forceDelete(int $id): void
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $customer);
        $customer->forceDelete();
        session()->flash('success', 'Customer permanently deleted.');
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

        Customer::query()->whereKey($ids)->get()->each(function (Customer $customer): void {
            match ($this->bulkAction) {
                'delete' => $this->bulkDelete($customer),
                'assign' => $this->bulkAssign($customer),
                default => null,
            };
        });

        $this->selectedIds = [];
        session()->flash('success', __('Bulk action completed.'));

        return null;
    }

    protected function filterViewResource(): string
    {
        return 'customers';
    }

    protected function currentFilterViewState(): array
    {
        return [
            'search' => $this->search,
            'showTrashed' => $this->showTrashed,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ];
    }

    protected function applyFilterViewState(array $filters): void
    {
        $this->search = (string) ($filters['search'] ?? '');
        $this->showTrashed = (bool) ($filters['showTrashed'] ?? false);
        $this->sortField = in_array($filters['sortField'] ?? '', ['name', 'created_at'], true) ? $filters['sortField'] : 'created_at';
        $this->sortDirection = ($filters['sortDirection'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
    }

    public function render()
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::query()
            ->when($this->showTrashed, fn ($q) => $q->onlyTrashed())
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('company', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%');
            }))
            ->withCount(['invoices', 'proposals'])
            ->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.customers.customer-index', [
            'customers' => $query->paginate(15),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'savedFilterViews' => $this->savedFilterViews(),
        ])->layout('layouts.app');
    }

    private function selectedIds(): array
    {
        return collect($this->selectedIds)->map(fn (mixed $id): int => (int) $id)->filter()->unique()->values()->all();
    }

    private function bulkDelete(Customer $customer): void
    {
        $this->authorize('delete', $customer);
        $customer->delete();
    }

    private function bulkAssign(Customer $customer): void
    {
        if ($this->bulkUserId === null) {
            return;
        }

        $this->authorize('update', $customer);
        $customer->forceFill(['user_id' => $this->bulkUserId])->save();
    }

    private function exportSelected(array $ids): StreamedResponse
    {
        return response()->streamDownload(function () use ($ids): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Phone', 'Company']);

            Customer::query()->whereKey($ids)->orderBy('name')->each(function (Customer $customer) use ($handle): void {
                $this->authorize('view', $customer);
                fputcsv($handle, [$customer->name, $customer->email, $customer->phone, $customer->company]);
            });

            fclose($handle);
        }, 'customers-selected.csv');
    }
}
