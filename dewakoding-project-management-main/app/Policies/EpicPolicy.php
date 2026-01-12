<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Epic;
use Illuminate\Auth\Access\HandlesAuthorization;

class EpicPolicy
{
    use HandlesAuthorization;
    
    public function before(AuthUser $authUser, $ability)
    {
        if ($authUser->isSuperAdmin()) {
            return true;
        }
    }
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'view_any_epic')->exists();
    }

    public function view(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->permissions()->where('name', 'view_epic')->exists();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'create_epic')->exists();
    }

    public function update(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->permissions()->where('name', 'update_epic')->exists();
    }

    public function delete(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->permissions()->where('name', 'delete_epic')->exists();
    }

    public function restore(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->permissions()->where('name', 'restore_epic')->exists();
    }

    public function forceDelete(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_epic')->exists();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'force_delete_any_epic')->exists();
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'restore_any_epic')->exists();
    }

    public function replicate(AuthUser $authUser, Epic $epic): bool
    {
        return $authUser->permissions()->where('name', 'replicate_epic')->exists();
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->permissions()->where('name', 'reorder_epic')->exists();
    }

}