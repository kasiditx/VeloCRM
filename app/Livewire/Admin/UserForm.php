<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserForm extends Component
{
    public ?int $userId = null;

    public ?User $user = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = 'Staff';

    public ?int $customer_id = null;

    public bool $is_active = true;

    public function mount(?int $userId = null): void
    {
        $this->userId = $userId;

        if (! $userId) {
            return;
        }

        $this->user = User::with('roles')->findOrFail($userId);
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->role = $this->user->getRoleNames()->first() ?? 'Staff';
        $this->customer_id = $this->user->customer_id;
        $this->is_active = $this->user->is_active;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'password' => [$this->userId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['Admin', 'Staff', 'Customer'])],
            'customer_id' => ['nullable', Rule::requiredIf($this->role === 'Customer'), 'exists:customers,id'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->user && $this->user->is(auth()->user()) && ! $data['is_active']) {
            session()->flash('error', 'You cannot deactivate your own account.');

            return;
        }

        if ($this->user && $this->user->hasRole('Admin') && $this->role !== 'Admin' && User::role('Admin')->count() <= 1) {
            session()->flash('error', 'At least one admin account must remain.');

            return;
        }

        if ($this->user && $this->user->hasRole('Admin') && ! $data['is_active'] && User::role('Admin')->active()->count() <= 1) {
            session()->flash('error', 'At least one active admin account is required.');

            return;
        }

        $attributes = [
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $data['is_active'],
        ];

        if (! empty($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        if ($this->userId) {
            $user = $this->user;
            $user->fill($attributes);
        } else {
            $user = new User($attributes);
        }

        $user->customer_id = $data['role'] === 'Customer' ? $data['customer_id'] : null;
        $user->save();
        $user->syncRoles([$this->role]);

        session()->flash('success', $this->userId ? 'User updated successfully.' : 'User created successfully.');

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.users.form', [
            'customers' => Customer::query()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
