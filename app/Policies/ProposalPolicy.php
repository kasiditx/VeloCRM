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
        return $user->hasRole('Admin') || $proposal->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Proposal $proposal): bool
    {
        return $user->hasRole('Admin') || $proposal->user_id === $user->id;
    }

    public function delete(User $user, Proposal $proposal): bool
    {
        return $user->hasRole('Admin') || $proposal->user_id === $user->id;
    }

    public function restore(User $user, Proposal $proposal): bool
    {
        return $user->hasRole('Admin') || $proposal->user_id === $user->id;
    }

    public function forceDelete(User $user, Proposal $proposal): bool
    {
        return $user->hasRole('Admin');
    }
}
