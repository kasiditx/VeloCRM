<?php

declare(strict_types=1);

namespace App\Livewire\Portal;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $customerName = '';

    public string $phone = '';

    public string $company = '';

    public string $address = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $customer = $this->customerFor($user->customer_id);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->customerName = $customer?->name ?? '';
        $this->phone = $customer?->phone ?? '';
        $this->company = $customer?->company ?? '';
        $this->address = $customer?->address ?? '';
    }

    public function save(): void
    {
        $user = Auth::user();
        $customer = $this->customerFor($user->customer_id);

        abort_unless($customer, 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'customerName' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $customer->fill([
            'name' => $validated['customerName'],
            'phone' => $validated['phone'],
            'company' => $validated['company'],
            'address' => $validated['address'],
        ]);
        $customer->save();

        $this->reset('current_password', 'password', 'password_confirmation');

        session()->flash('success', __('Profile updated successfully.'));
    }

    public function render()
    {
        return view('livewire.portal.profile')
            ->layout('layouts.portal')
            ->title(__('Portal Profile'));
    }

    protected function customerFor(?int $customerId): ?Customer
    {
        return $customerId ? Customer::withoutGlobalScopes()->find($customerId) : null;
    }
}
