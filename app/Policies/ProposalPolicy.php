<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Proposal $proposal): bool
    {
        if ($user->hasRole('Customer')) {
            return $user->customer_id !== null && $proposal->customer_id === $user->customer_id;
        }

        return $user->hasRole('Admin') || $proposal->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return ! $user->hasRole('Customer');
    }

    public function update(User $user, Proposal $proposal): bool
    {
        if ($user->hasRole('Customer')) {
            return false;
        }

        return $user->hasRole('Admin') || $proposal->user_id === $user->id;
    }

    public function respond(User $user, Proposal $proposal): bool
    {
        return $user->hasRole('Customer')
            && $user->customer_id !== null
            && $proposal->customer_id === $user->customer_id;
    }

    public function delete(User $user, Proposal $proposal): bool
    {
        if ($user->hasRole('Customer')) {
            return false;
        }

        return $user->hasRole('Admin') || $proposal->user_id === $user->id;
    }

    public function restore(User $user, Proposal $proposal): bool
    {
        if ($user->hasRole('Customer')) {
            return false;
        }

        return $user->hasRole('Admin') || $proposal->user_id === $user->id;
    }

    public function forceDelete(User $user, Proposal $proposal): bool
    {
        return $user->hasRole('Admin');
    }
}
