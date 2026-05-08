<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->hasRole('Admin') || $customer->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasRole('Admin') || $customer->user_id === $user->id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasRole('Admin') || $customer->user_id === $user->id;
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $user->hasRole('Admin') || $customer->user_id === $user->id;
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->hasRole('Admin');
    }
}
