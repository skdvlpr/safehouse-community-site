<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function view(User $user, User $model): bool
    {
        return $this->canManageTarget($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(User $user, User $model): bool
    {
        return $this->canManageTarget($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $this->canManageTarget($user, $model);
    }

    private function canManageTarget(User $user, User $model): bool
    {
        if (! $user->canManageUsers()) {
            return false;
        }

        if ($model->hasRole('super-admin') && ! $user->hasRole('super-admin')) {
            return false;
        }

        return true;
    }
}
