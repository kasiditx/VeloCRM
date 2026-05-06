<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $userId): void
    {
        $user = User::with('roles')->findOrFail($userId);

        if ($user->is(auth()->user())) {
            session()->flash('error', 'You cannot deactivate your own account.');

            return;
        }

        if ($user->is_active && $user->hasRole('Admin') && User::role('Admin')->active()->count() <= 1) {
            session()->flash('error', 'At least one active admin account is required.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);

        session()->flash('success', 'User status updated successfully.');
    }

    public function delete(int $userId): void
    {
        $user = User::with('roles')->findOrFail($userId);

        if ($user->is(auth()->user())) {
            session()->flash('error', 'You cannot delete your own account.');

            return;
        }

        if ($user->hasRole('Admin') && User::role('Admin')->count() <= 1) {
            session()->flash('error', 'At least one admin account must remain.');

            return;
        }

        $user->delete();

        session()->flash('success', 'User deleted successfully.');
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->when($this->search, fn ($query) => $query->where(function ($subQuery) {
                $subQuery
                    ->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            }))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.users.index', [
            'users' => $users,
        ])->layout('layouts.app');
    }
}
