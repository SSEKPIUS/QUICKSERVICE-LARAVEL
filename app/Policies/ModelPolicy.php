<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

abstract class ModelPolicy
{
    use HandlesAuthorization;

    abstract protected function getModelClass(): string;

    public function viewAny(User $user)
    {
        return $user->can('view-any-' . $this->getModelClass());
    }

    public function view(User $user, Model $model)
    {
        if ($user->can('view-' . $this->getModelClass())) {
            return true;
        }

        if ($user->can('view-self-' . $this->getModelClass())) {
            return $this->isOwner($user, $model);
        }

        return false;
    }

    public function create(User $user)
    {
        return $user->can('create-' . $this->getModelClass());
    }

    public function update(User $user, Model $model)
    {
        if ($user->can('update-' . $this->getModelClass())) {
            return true;
        }

        if ($user->can('update-self-' . $this->getModelClass())) {
            return $this->isOwner($user, $model);
        }

        return false;
    }

    public function delete(User $user, Model $model)
    {
        if ($user->can('delete-' . $this->getModelClass())) {
            return true;
        }

        if ($user->can('delete-self-' . $this->getModelClass())) {
            return $this->isOwner($user, $model);
        }

        return false;
    }

    private function isOwner(User $user, Model $model): bool
    {
        if (!empty($user) && method_exists($model, 'user')) {
            return $user->getKey() === $model->getRelation('user')->getKey();
        }

        return false;
    }
}