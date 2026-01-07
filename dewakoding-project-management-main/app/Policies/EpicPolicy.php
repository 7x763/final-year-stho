<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Epic;
use Illuminate\Auth\Access\HandlesAuthorization;

class EpicPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_epic');
    }

    public function view(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->can('view_epic');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_epic');
    }

    public function update(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->can('update_epic');
    }

    public function delete(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->can('delete_epic');
    }

    public function restore(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->can('restore_epic');
    }

    public function forceDelete(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->can('force_delete_epic');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_epic');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_epic');
    }

    public function replicate(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->can('replicate_epic');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_epic');
    }

}