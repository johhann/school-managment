<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

class GradePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_grade');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Grade $grade): bool
    {
        return $user->can('view_grade');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_grade');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Grade $grade): bool
    {
        return $user->can('update_grade');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Grade $grade): bool
    {
        return $user->can('delete_grade');
    }
}
