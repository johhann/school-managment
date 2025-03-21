<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_subject');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Subject $subject): bool
    {
        return $user->can('view_subject');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_subject');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Subject $subject): bool
    {
        return $user->can('update_subject');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Subject $subject): bool
    {
        return $user->can('delete_subject');
    }
}
