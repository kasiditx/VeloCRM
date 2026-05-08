<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Note $note): bool
    {
        return $user->hasRole('Admin') || $note->user_id === $user->id;
    }
}
