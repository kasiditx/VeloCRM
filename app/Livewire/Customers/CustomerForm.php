<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Livewire\Concerns\HandlesCustomFields;
use App\Models\Customer;
use App\Models\Lead;
use App\Rules\ThaiTaxId;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class CustomerForm extends Component
{
    use AuthorizesRequests, HandlesCustomFields;

    public ?int $customerId = null;

    public ?Customer $customer = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $company = '';

    public string $address = '';

    public string $tax_id = '';

    public string $branch = '';

    public ?int $lead_id = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'tax_id' => ['nullable', 'digits:13', new ThaiTaxId],
            'branch' => 'nullable|string|max:255',
            'lead_id' => 'nullable|exists:leads,id',
        ];
    }

    public function mount(?int $customerId = null): void
    {
        $this->customerId = $customerId;

        if ($customerId) {
            $this->customer = Customer::findOrFail($customerId);
            $this->authorize('update', $this->customer);

            $this->name = $this->customer->name;
            $this->email = $this->customer->email ?? '';
            $this->phone = $this->customer->phone ?? '';
            $this->company = $this->customer->company ?? '';
            $this->address = $this->customer->address ?? '';
            $this->tax_id = $this->customer->tax_id ?? '';
            $this->branch = $this->customer->branch ?? '';
            $this->lead_id = $this->customer->lead_id;
            $this->loadCustomFields(Customer::class, $this->customer);
        } else {
            $this->authorize('create', Customer::class);
            $this->loadCustomFields(Customer::class);
        }
    }

    public function save(): void
    {
        $data = $this->validate($this->rules() + $this->customFieldRules());
        $customFieldValues = $data['customFieldValues'] ?? [];
        unset($data['customFieldValues']);
        $data['tax_id'] = $data['tax_id'] !== '' ? preg_replace('/\D/', '', $data['tax_id']) : null;

        if ($this->customerId) {
            $this->authorize('update', $this->customer);
            $this->customer->update($data);
            $this->customer->syncCustomFieldValues($customFieldValues);
            session()->flash('success', 'Customer updated successfully.');
            $this->redirect(route('customers.show', $this->customerId), navigate: true);
        } else {
            $this->authorize('create', Customer::class);
            $customer = new Customer($data);
            $customer->user_id = auth()->id();
            $customer->save();
            $customer->syncCustomFieldValues($customFieldValues);
            session()->flash('success', 'Customer created successfully.');
            $this->redirect(route('customers.show', $customer->id), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.customers.customer-form', [
            'leads' => Lead::whereDoesntHave('customer')->orWhere('id', $this->lead_id)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
